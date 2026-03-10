from odoo import models, fields, api

class ResPartner(models.Model):
    _inherit = 'res.partner'

    # KYC Fields
    pan_card = fields.Char(string="PAN Card ID", tracking=True)
    pan_card_file = fields.Binary(string="PAN Card Document")
    aadhaar_card = fields.Char(string="Aadhaar ID", tracking=True)
    aadhaar_card_file = fields.Binary(string="Aadhaar Document")
    
    kyc_status = fields.Selection([
        ('pending', 'Pending'),
        ('verified', 'Verified'),
        ('rejected', 'Rejected')
    ], string="KYC Status", default='pending', tracking=True)

    # Risk Profile Link
    risk_profile_id = fields.Many2one('stockidea.risk', string="Risk Profile")

    # Alternate Contacts for Deep Search
    alt_phone_1 = fields.Char(string="Alternate Phone 1")
    alt_phone_2 = fields.Char(string="Alternate Phone 2")

    def action_create_upsell(self):
        """ One-Click Upsell: Creates a new Sale Order for this customer """
        self.ensure_one()
        return {
            'name': 'Upsell Opportunity',
            'type': 'ir.actions.act_window',
            'res_model': 'sale.order',
            'view_mode': 'form',
            'target': 'current',
            'context': {
                'default_partner_id': self.id,
                'default_user_id': self.env.user.id
            }
        }
