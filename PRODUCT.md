# VDOT Product Information

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Consumers who scan a QR code on food, beverage, or pharmaceutical packaging and need clear, trustworthy product information on a phone. VDOT administrators create and maintain the underlying product and unit records.

## Product Purpose

Provide a public, mobile-first product-information page for products and physical units created in VDOT. Success means a consumer can identify the product, understand its ingredients or composition, allergens or warnings, usage, nutrition or pharmaceutical information, provenance, and responsible company without signing in.

## Positioning

Public product information is generated from the same controlled VDOT record used for product identity and traceability, while each field remains private unless an administrator explicitly marks it public.

## Operating Context

- Consumers enter through a QR scan on physical packaging.
- Public pages are served at `product.ocmo.co.za`.
- VDOT remains the system in which product models, physical units, companies, and custom fields are administered.
- Food, beverage, and pharmaceutical products are the initial supported categories.
- A physical unit may contribute batch-specific information while inheriting product information from its VDOT model and fieldset.

## Capabilities and Constraints

- Every public custom field requires an explicit `Public` opt-in in VDOT; private is the default.
- Encrypted fields must never be exposed publicly, regardless of other settings.
- Public responses must contain only an allowlisted product identity envelope and opted-in custom fields.
- The public experience must support product overview, ingredients or composition, allergens, nutrition or dosage, warnings, usage and storage, traceability, certifications, packaging or recycling, and company information when those fields exist.
- Missing sections are omitted cleanly rather than filled with invented content.
- URLs must remain stable for printed QR codes.
- Scan tracking may record aggregate product access without exposing private VDOT data.
- The application is deployed on the existing Dokploy host used by VDOT.

## Brand Commitments

- Product name: VDOT Product Information.
- Public host: `product.ocmo.co.za`.
- The experience should feel trustworthy, factual, calm, and easy to use at shelf-side mobile scale.
- The supplied SmartLabel example is a functional reference for consistent information grouping, not a visual-copying requirement.

## Evidence on Hand

- Existing VDOT Laravel application and production deployment.
- Existing public verification controller and Blade surface at `/ht/{asset_tag}`.
- Existing VDOT product models, physical-unit assets, companies, custom fieldsets, images, and uploaded documents.
- SmartLabel reference supplied by Josh: detailed product information grouped into predictable consumer-facing sections.
- No product claims, nutrition values, ingredients, certifications, or regulatory approvals may be fabricated.

## Product Principles

1. Private by default; disclosure is always deliberate.
2. Put safety-critical information before promotional information.
3. Preserve one controlled source from VDOT record to public page.
4. Make scanned information understandable within seconds on a phone.
5. Show provenance and update context so consumers can judge freshness.

## Accessibility & Inclusion

The public experience must be keyboard accessible, screen-reader friendly, responsive, readable in bright mobile conditions, and structured with semantic headings and navigation. It should meet WCAG 2.2 AA for the implemented surface.
