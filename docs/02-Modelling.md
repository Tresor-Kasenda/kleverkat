# Modélisation de Données

## 1. Diagramme Entité-Relation (Conceptuel)

```
Sector
  ├── id
  ├── name (ex: Assurance, Banque)
  ├── slug
  └── description

Company (Partenaire)
  ├── id
  ├── name
  ├── slug
  ├── logo
  ├── description
  ├── website
  ├── email_contact
  ├── phone
  ├── is_active
  ├── user_id (compte de connexion)
  └── sector_id

ProductCategory
  ├── id
  ├── name (ex: Auto, Habitation, Santé)
  ├── slug
  ├── description
  ├── icon
  ├── sector_id
  └── parent_id (auto-référence pour sous-catégories)

Product
  ├── id
  ├── name (ex: Assurance Auto Tiers)
  ├── slug
  ├── description
  ├── short_description
  ├── price_range_min
  ├── price_range_max
  ├── highlights (JSON)
  ├── is_active
  ├── is_featured
  ├── sort_order
  ├── company_id
  └── category_id

ProductDetail (Garanties / Caractéristiques)
  ├── id
  ├── label (ex: "Garantie bris de glace")
  ├── value (ex: "Incluse" / "Optionnelle" / "Non incluse")
  ├── is_highlight
  ├── sort_order
  └── product_id

Questionnaire
  ├── id
  ├── name (ex: "Questionnaire Auto Tiers")
  ├── description
  ├── is_active
  ├── category_id (optionnel, rattaché à une catégorie)
  └── product_id (optionnel, rattaché à un produit spécifique)

Question
  ├── id
  ├── label (texte de la question)
  ├── type (text, number, select, boolean, date)
  ├── options (JSON pour les select)
  ├── placeholder
  ├── is_required
  ├── sort_order
  ├── section (ex: "Profil", "Véhicule", "Conducteur")
  ├── helper_text
  └── questionnaire_id

ScoringCoefficient
  ├── id
  ├── question_id
  ├── company_id (pour surcharge partenaire)
  ├── coefficient (float, ex: 1.0, 1.5, 0.8)
  └── logic (JSON pour règles conditionnelles)

Lead
  ├── id
  ├── first_name
  ├── last_name
  ├── email
  ├── phone
  ├── answers (JSON — réponses complètes)
  ├── score (calulé)
  ├── status (new, contacted, qualified, converted, lost)
  ├── product_id
  └── company_id

LeadAction
  ├── id
  ├── type (viewed, contact_requested, quote_requested, redirect)
  ├── metadata (JSON)
  ├── lead_id
  └── company_id

User
  ├── id (existant via Fortify)
  ├── role (admin, partner, user)
  ├── company_id (si role = partner)
  └── ...
```

## 2. Relations Clés

```
Sector 1──N Company
Sector 1──N ProductCategory
ProductCategory 1──N ProductCategory (parent)
Company 1──N Product
ProductCategory 1──N Product
Product 1──N ProductDetail
ProductCategory 1──N Questionnaire
Product 1──N Questionnaire
Questionnaire 1──N Question
Question 1──N ScoringCoefficient
Company 1──N ScoringCoefficient
Company 1──N Lead
Product 1──N Lead
Lead 1──N LeadAction
User 1──1 Company (si partner)
```

## 3. Migrations (colonnes principales)

```sql
-- sectors
CREATE TABLE sectors (
    id bigint PRIMARY KEY AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    description text NULL,
    timestamps
);

-- companies
CREATE TABLE companies (
    id bigint PRIMARY KEY AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    logo varchar(255) NULL,
    description text NULL,
    website varchar(255) NULL,
    email_contact varchar(255) NULL,
    phone varchar(50) NULL,
    is_active boolean DEFAULT true,
    user_id bigint NULL REFERENCES users(id),
    sector_id bigint NOT NULL REFERENCES sectors(id),
    timestamps
);

-- product_categories
CREATE TABLE product_categories (
    id bigint PRIMARY KEY AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    description text NULL,
    icon varchar(255) NULL,
    sector_id bigint NOT NULL REFERENCES sectors(id),
    parent_id bigint NULL REFERENCES product_categories(id),
    timestamps
);

-- products
CREATE TABLE products (
    id bigint PRIMARY KEY AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL UNIQUE,
    description text NULL,
    short_description varchar(500) NULL,
    price_range_min decimal(10,2) NULL,
    price_range_max decimal(10,2) NULL,
    highlights json NULL,
    is_active boolean DEFAULT true,
    is_featured boolean DEFAULT false,
    sort_order integer DEFAULT 0,
    company_id bigint NOT NULL REFERENCES companies(id),
    category_id bigint NOT NULL REFERENCES product_categories(id),
    timestamps
);

-- product_details
CREATE TABLE product_details (
    id bigint PRIMARY KEY AUTO_INCREMENT,
    label varchar(255) NOT NULL,
    value varchar(255) NOT NULL,
    is_highlight boolean DEFAULT false,
    sort_order integer DEFAULT 0,
    product_id bigint NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    timestamps
);

-- questionnaires
CREATE TABLE questionnaires (
    id bigint PRIMARY KEY AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description text NULL,
    is_active boolean DEFAULT true,
    category_id bigint NULL REFERENCES product_categories(id),
    product_id bigint NULL REFERENCES products(id),
    timestamps
);

-- questions
CREATE TABLE questions (
    id bigint PRIMARY KEY AUTO_INCREMENT,
    label text NOT NULL,
    type varchar(50) NOT NULL,
    options json NULL,
    placeholder varchar(255) NULL,
    is_required boolean DEFAULT true,
    sort_order integer DEFAULT 0,
    section varchar(255) NULL,
    helper_text text NULL,
    questionnaire_id bigint NOT NULL REFERENCES questionnaires(id) ON DELETE CASCADE,
    timestamps
);

-- scoring_coefficients
CREATE TABLE scoring_coefficients (
    id bigint PRIMARY KEY AUTO_INCREMENT,
    coefficient decimal(5,2) DEFAULT 1.00,
    logic json NULL,
    question_id bigint NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
    company_id bigint NULL REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE(question_id, company_id),
    timestamps
);

-- leads
CREATE TABLE leads (
    id bigint PRIMARY KEY AUTO_INCREMENT,
    first_name varchar(255) NULL,
    last_name varchar(255) NULL,
    email varchar(255) NULL,
    phone varchar(50) NULL,
    answers json NOT NULL,
    score decimal(10,2) NULL,
    status varchar(50) DEFAULT 'new',
    product_id bigint NOT NULL REFERENCES products(id),
    company_id bigint NOT NULL REFERENCES companies(id),
    timestamps
);

-- lead_actions
CREATE TABLE lead_actions (
    id bigint PRIMARY KEY AUTO_INCREMENT,
    type varchar(50) NOT NULL,
    metadata json NULL,
    lead_id bigint NOT NULL REFERENCES leads(id) ON DELETE CASCADE,
    company_id bigint NULL REFERENCES companies(id),
    timestamps
);
```
