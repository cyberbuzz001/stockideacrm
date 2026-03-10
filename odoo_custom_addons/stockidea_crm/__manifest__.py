{
    'name': 'StockIdea CRM',
    'version': '1.0',
    'category': 'Sales/CRM',
    'summary': 'Custom CRM for Advisory Firm (Lifecycle, KYC, Compliance)',
    'description': """
        StockIdea Custom CRM Module.
        Phase 1: Core Data Structure, KYC, Lifecycle.
        Phase 2: Revenue & Ledger.
        Phase 3: Productivity (Heartbeat).
        Phase 4: AI Suite.
    """,
    'depends': ['base', 'crm', 'sale_management', 'hr', 'mail'],
    'data': [
        'security/ir.model.access.csv',
        'views/crm_lead_views.xml',
        'views/res_partner_views.xml',
        'views/risk_profile_views.xml',
        'views/sale_order_views.xml',
        'views/admin_dashboard_views.xml',
        'wizard/create_installment_views.xml',
        'data/ir_cron.xml',
        'data/ir_cron_logout.xml',
    ],
    'assets': {
        'web.assets_backend': [
            'stockidea_crm/static/src/scss/custom_styles.scss',
            'stockidea_crm/static/src/js/heartbeat.js',
        ],
    },
    'installable': True,
    'application': True,
    'license': 'LGPL-3',
}
