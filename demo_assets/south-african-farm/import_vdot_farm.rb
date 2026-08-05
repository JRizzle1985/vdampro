#!/usr/bin/env ruby

require "csv"
require "date"
require "json"
require "net/http"
require "uri"

BASE = "https://vdot.veridot.co.za/api/v1"
ROOT = File.expand_path(__dir__)
token_source = if ENV["VDOT_TOKEN_FILE"]
                 File.read(ENV["VDOT_TOKEN_FILE"])
               else
                 `pbpaste`
               end
TOKEN = token_source.gsub(/\s+/, "")
abort "No API token found in clipboard" if TOKEN.empty?

def request(path, method: "GET", body: nil)
  sleep 1.1
  uri = URI.join("#{BASE}/", path)
  http = Net::HTTP.new(uri.host, uri.port)
  http.use_ssl = true
  req = case method
        when "POST" then Net::HTTP::Post.new(uri)
        else Net::HTTP::Get.new(uri)
        end
  req["Authorization"] = "Bearer #{TOKEN}"
  req["Accept"] = "application/json"
  req["Content-Type"] = "application/json"
  req.body = JSON.generate(body) if body
  res = http.request(req)
  parsed = JSON.parse(res.body) rescue { "raw" => res.body[0, 500] }
  abort "#{method} #{path} failed (#{res.code}): #{JSON.generate(parsed)}" unless res.is_a?(Net::HTTPSuccess)
  parsed
end

def rows(response)
  value = response["rows"] || response["data"] || response["payload"] || []
  value.is_a?(Array) ? value : [value].compact
end

def record(response)
  value = response["payload"] || response["data"] || response
  value.is_a?(Hash) && value["id"] ? value : value["payload"]
end

def find_by_name(endpoint, name)
  response = request("#{endpoint}?name=#{URI.encode_www_form_component(name)}")
  rows(response).find { |row| row["name"].to_s == name }
end

def ensure_named(endpoint, name, payload)
  existing = find_by_name(endpoint, name)
  return [existing["id"], false] if existing
  created = record(request(endpoint, method: "POST", body: payload))
  abort "Could not read created #{endpoint} record for #{name}" unless created && created["id"]
  [created["id"], true]
end

def read_csv(name)
  CSV.read(File.join(ROOT, name), headers: true)
end

created = Hash.new(0)
ids = { categories: {}, manufacturers: {}, locations: {}, models: {} }

read_csv("categories.csv").each do |row|
  name = row["Name"]
  id, fresh = ensure_named("categories", name, {
    name: name, tag_color: row["Tag Color"], category_type: row["Category Type"],
    checkin_email: false, use_default_eula: false, require_acceptance: false,
    alert_on_response: false, notes: row["Notes"]
  })
  ids[:categories][name] = id
  created[:categories] += 1 if fresh
end

read_csv("manufacturers.csv").each do |row|
  name = row["Name"]
  id, fresh = ensure_named("manufacturers", name, {
    name: name, tag_color: row["Tag Color"], url: row["URL"], support_url: row["Support URL"],
    warranty_lookup_url: row["Warranty Lookup URL"], support_phone: row["Support Phone"],
    support_email: row["Support Email"], notes: row["Notes"]
  })
  ids[:manufacturers][name] = id
  created[:manufacturers] += 1 if fresh
end

read_csv("locations.csv").each do |row|
  name = row["name"]
  id, fresh = ensure_named("locations", name, {
    name: name, address: row["address"], address2: row["address2"], city: row["city"],
    state: row["state"], country: row["country"], zip: row["zip"], notes: row["notes"],
    phone: row["phone"], fax: row["fax"], currency: row["currency"], tag_color: row["tag color"]
  })
  ids[:locations][name] = id
  created[:locations] += 1 if fresh
end

read_csv("models.csv").each do |row|
  name = row["Name"]
  existing = find_by_name("models", name)
  id = existing && existing["id"]
  unless id
    created_model = record(request("models", method: "POST", body: {
      name: name, model_number: row["Model Number"], min_amt: row["Minimum Amount"].to_i,
      manufacturer_id: ids[:manufacturers].fetch(row["Manufacturer"]),
      category_id: ids[:categories].fetch(row["Category"]), require_serial: row["Require Serial"] == "true",
      eol: row["EOL"].to_i, notes: row["Notes"], requestable: row["Requestable"] == "true"
    }))
    id = created_model["id"]
    created[:models] += 1
  end
  ids[:models][name] = id
end

status = rows(request("statuslabels?name=Ready%20to%20Deploy")).find { |row| row["name"] == "Ready to Deploy" }
abort "Ready to Deploy status label was not found" unless status

assets_created = 0
assets_skipped = 0
read_csv("assets.csv").each do |row|
  tag = row["Asset Tag"]
  existing = rows(request("hardware?asset_tag=#{URI.encode_www_form_component(tag)}")).find { |asset| asset["asset_tag"] == tag }
  if existing
    assets_skipped += 1
    next
  end
  request("hardware", method: "POST", body: {
    asset_tag: tag, name: row["Name"], model_id: ids[:models].fetch(row["Model"]), status_id: status["id"],
    location_id: ids[:locations].fetch(row["Location"]), order_number: row["Order Number"],
    purchase_date: Date.strptime(row["Purchase Date"], "%m/%d/%y").strftime("%Y-%m-%d"),
    purchase_cost: row["Purchase Cost"], notes: row["Asset Notes"],
    asset_eol_date: row["Asset EOL Date"].to_s.empty? ? nil : Date.strptime(row["Asset EOL Date"], "%m/%d/%y").strftime("%Y-%m-%d")
  })
  assets_created += 1
end

puts JSON.generate({ created: created, assets_created: assets_created, assets_skipped: assets_skipped })
