# South African farm demo import pack

This pack is designed for a Snipe-IT/vdampro demonstration of a fictional mixed farm, **Klipfontein Mixed Farm**, in the Free State. It includes farm machinery, vehicles, irrigation and workshop equipment, livestock, and operational infrastructure.

## Import order

Import the files in this order so the display-name references resolve:

1. `categories.csv`
2. `manufacturers.csv`
3. `locations.csv`
4. `models.csv`
5. `assets.csv`

After the base CSV/API import, run `php demo_assets/south-african-farm/enrich_vdot_farm.php` from the application root. The idempotent enrichment creates the `Klipfontein Mixed Farm` company, assigns all `KMF-*` assets to it, attaches the existing `Microdot pin` field through a farm fieldset, generates a unique demo PIN for every asset, and creates five fictional farm staff profiles.

## Demo conventions

- Asset tags use the `KMF-` prefix and are fictional.
- Livestock are represented as individual assets; the asset tag acts as the farm's visual identification number. In a production deployment, use a custom field for official ear-tag, RFID, breed, birth date, and medical records.
- Costs are illustrative South African rand values, not valuations or purchasing advice.
- The dataset intentionally mixes available, field-based, and high-value assets so dashboards, locations, search, reports, and audit workflows have useful variety.
- Import the support records before `assets.csv`; the asset importer matches category, manufacturer, location, and model by name.
- Each farm asset page has a scoped **Open in RadarEye demo** button that deep-links to the same `KMF-*` asset in the RadarEye simulation.
- Staff addresses use the reserved `.example` domain and passwords are random, unrecoverable values. They are demo assignment profiles, not real mailboxes or issued login accounts.

## Suggested demo storyline

Start at the dashboard, filter by `Machinery - Tractors`, open a tractor record, show its location and notes, then search `KMF-COW` to demonstrate individually tracked livestock. Finish with a location report for `Cattle Handling Kraal`, `Workshop`, or `Borehole 1`.
