/**
 * AUTO-GENERATED config.js — do not edit CUSTOM_KPIS manually
 */
window.DASHBOARD_CONFIG = {
    DATA_FOLDER: 'sales_data',
    CUSTOMER_MASTER_FILE: 'Customer_Master_Report_24092025.xlsx',
    INVOICE_DATA_FILES: ["invoice.xlsx"],
    EXCLUDED_PRODUCTS_LIST: ["TW SHINER SPONGE","CHAIN LUBE","CHAIN CLEANER","BRAKE CLEANER","FUELINJECT","ANTI RUST LUB SPRAY","THROTTLEBODYCLEANER","MICRO FBR CLOTH","AIOHELMET CLEANER","TW SHINER 3 IN 1"],
    POWER1_PRODUCTS_LIST: ["POWER1 4T 10W-30, 10X.9L MK","POWER1 4T 10W-30, 10X1L MK","POWER1 4T 15W-40, 10X1L MK","POWER1 CRUISE4T 20W50 10X1.2HMK","POWER1 CRUISE 4T20W-50,10X1L","POWER1 ULTIMATE4T10W-40,6X1LMK","POWER1CRUISE4T 15W50,4X2.5L MK"],
    ACTIV_BRANDS_INCLUDE:    ["ACTIV"],
    ACTIV_BRANDS_EXCLUDE:    ["ACTIV ESSENTIAL"],
    MAGNATEC_BRANDS_INCLUDE: ["MAGNATEC","MAGNATEC SUV","MAGNATEC DIESEL"],
    CRB_BRANDS_INCLUDE:      ["CRB TURBOMAX"],
    AUTOCARE_BRANDS_INCLUDE: ["AUTO CARE EXTERIOR","AUTO CARE MAINTENANCE"],
    COLUMNS: {"INVOICE_DATE":"Invoice Date","SE_NAME":"Sales Executive Name","CUSTOMER_NAME":"Customer Name","CUSTOMER_CODE":"Customer Code","BRAND_NAME":"Product Brand Name","PRODUCT_NAME":"Product Name","PRODUCT_VOLUME":"Product Volume","PACK_SIZE":"Pack Size","TOTAL_VALUE":"Total Value incl VAT/GST","SECTOR_NAME":"Sector Name","MASTER_CUSTOMER_CODE":"Customer Code","MASTER_CUSTOMER_NAME":"Customer Name","MASTER_SE_NAME":"Sales Executive"},
    BUILTIN_KPIS: [
  {
    "id": "volumeBySE",
    "name": "Volume by Sales Exec",
    "type": "volume",
    "threshold": 0,
    "sectors": [],
    "brands": [],
    "products": [],
    "targets": {},
    "isBuiltin": true,
    "excludeProducts": true,
    "excludeAutocare": true
  },
  {
    "id": "weeklySales",
    "name": "Weekly Sales Volume",
    "type": "volume",
    "threshold": 0,
    "sectors": [],
    "brands": [],
    "products": [],
    "targets": {},
    "isBuiltin": true,
    "byWeek": true
  },
  {
    "id": "activ",
    "name": "'Activ' Customer Count",
    "type": "customer_count",
    "threshold": 9,
    "sectors": [],
    "brands": [
      "ACTIV",
      "Activ",
      "Activ Scooter",
      "Activ CRUISE"
    ],
    "products": [],
    "targets": {},
    "isBuiltin": true,
    "excludeBrands": [
      "ACTIV ESSENTIAL"
    ]
  },
  {
    "id": "power1",
    "name": "'Power1' Customer Count",
    "type": "customer_count",
    "threshold": 5,
    "sectors": [],
    "brands": [],
    "products": [],
    "targets": {},
    "isBuiltin": true,
    "useProductList": true
  },
  {
    "id": "magnatec",
    "name": "'Magnatec' Customer Count",
    "type": "customer_count",
    "threshold": 3.5,
    "sectors": [],
    "brands": [
      "MAGNATEC",
      "MAGNATEC SUV",
      "MAGNATEC DIESEL"
    ],
    "products": [],
    "targets": {},
    "isBuiltin": true
  },
  {
    "id": "crb",
    "name": "'CRB Turbomax' Count",
    "type": "customer_count",
    "threshold": 5,
    "sectors": [],
    "brands": [
      "CRB TURBOMAX",
      "CRB Turbomax"
    ],
    "products": [
      "CRB TURBOMAX15W-40 CH-4,10L",
      "CRB TURBOMAX15W-40 CH-4,15L",
      "CRB TURBOMAX15W-40CH-4,7.5L",
      "CRBTM15W40CI4PLUS,15L14+1LMK",
      "CRBTURBOMAX15W40CI4PLUS,10L",
      "CRBTURBOMAX15W40CI4PLUS,7.5L",
      "CRBTM15W-40 CH-4,15L14+1LMK"
    ],
    "targets": {},
    "isBuiltin": true
  },
  {
    "id": "highvol",
    "name": "High-Volume (9L) Count",
    "type": "customer_count",
    "threshold": 9,
    "sectors": [],
    "brands": [],
    "products": [],
    "targets": {},
    "isBuiltin": true,
    "excludeProducts": true
  },
  {
    "id": "autocare",
    "name": "Autocare Count",
    "type": "customer_count",
    "threshold": 5,
    "sectors": [],
    "brands": [
      "AUTO CARE EXTERIOR",
      "AUTO CARE MAINTENANCE",
      "Auto Care Maintenance",
      "Auto Care Exterior"
    ],
    "products": [
      "ANTI RUST LUBSPRAY,24X.075L MK",
      "CHAIN LUBE, 24X.105L R MK",
      "ANTI RUST LUB SPRAY,12X.42L MK",
      "BRAKE CLEANER, 12X.4L MK",
      "THROTTLEBODYCLEANER,12X.3L MK",
      "TW CHAIN CLEANER, 24X.11L MK",
      "TW 3-IN-1 SHINER, 48X.1L MK"
    ],
    "targets": {},
    "isBuiltin": true
  }
],
    CUSTOM_KPIS: [
  {
    "id": "custom_1773470428710",
    "name": "Radicool 10Ltr",
    "type": "customer_count",
    "sectors": [],
    "brands": [
      "Radicool"
    ],
    "products": [],
    "threshold": 10,
    "targets": {}
  },
  {
    "id": "custom_1776260753251",
    "name": "CRB Turbomax 7.5L or above pack",
    "type": "customer_count",
    "sectors": [],
    "brands": [
      "CRB Turbomax"
    ],
    "products": [
      "CRB TURBOMAX 15W-40 CH-4,11 MK",
      "CRB TURBOMAX15W-40 CH-4,10L",
      "CRBTM15W-40 CH-4,15L14+1LMK",
      "CRBTURBOMAX15W40CI4PLUS,15L",
      "CRBTM15W40CI4PLUS,15L14+1LMK",
      "CRBTURBOMAX15W40CI4PLUS,10L",
      "CRB TURBOMAX15W-40CH-4,7.5L"
    ],
    "threshold": 7.5,
    "targets": {
      "MR. DAYA RAM GEDAR": 14,
      "MR. NARESH KUMAR": 7,
      "MR. RAKESH KUMAR": 16,
      "Mr. GAJANAND BHATI": 3,
      "Mr. JAGDISH KUMAR THAREJA": 10
    }
  },
  {
    "id": "custom_1776260810216",
    "name": "GTX 5w30 5Ltr",
    "type": "customer_count",
    "sectors": [],
    "brands": [
      "GTX"
    ],
    "products": [
      "GTX 5W-30, 20X.5L H",
      "GTX 5W-30, 4X3.5L H P",
      "GTX 5W-30, 4X3L H P",
      "GTX 5W-30, 50L"
    ],
    "threshold": 5,
    "targets": {
      "MR. DAYA RAM GEDAR": 1,
      "MR. NARESH KUMAR": 8,
      "MR. RAKESH KUMAR": 12,
      "Mr. GAJANAND BHATI": 5,
      "Mr. JAGDISH KUMAR THAREJA": 10
    }
  },
  {
    "id": "custom_1776260917900",
    "name": "GTX 5w30 14ltr",
    "type": "customer_count",
    "sectors": [],
    "brands": [
      "GTX"
    ],
    "products": [
      "GTX 5W-30, 20X.5L H",
      "GTX 5W-30, 4X3.5L H P",
      "GTX 5W-30, 4X3L H P",
      "GTX 5W-30, 50L"
    ],
    "threshold": 14,
    "targets": {
      "MR. DAYA RAM GEDAR": 1,
      "MR. NARESH KUMAR": 8,
      "MR. RAKESH KUMAR": 12,
      "Mr. GAJANAND BHATI": 5,
      "Mr. JAGDISH KUMAR THAREJA": 10
    }
  },
  {
    "id": "custom_1776260989366",
    "name": "Activ 5w30 9L",
    "type": "customer_count",
    "sectors": [],
    "brands": [
      "Activ"
    ],
    "products": [
      "ACTIV 4T 5W-30 FS, 20X.9L MK"
    ],
    "threshold": 9,
    "targets": {
      "MR. DAYA RAM GEDAR": 6,
      "MR. NARESH KUMAR": 4,
      "MR. RAKESH KUMAR": 9,
      "Mr. GAJANAND BHATI": 8,
      "Mr. JAGDISH KUMAR THAREJA": 9
    }
  }
]
};