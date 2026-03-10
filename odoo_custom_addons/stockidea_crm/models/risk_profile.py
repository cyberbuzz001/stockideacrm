from odoo import models, fields, api

class RiskProfile(models.Model):
    _name = 'stockidea.risk'
    _description = 'Client Risk Profile (RPM)'
    _inherit = ['mail.thread', 'mail.activity.mixin']

    name = fields.Char(string="Profile Name", compute='_compute_name')
    partner_id = fields.Many2one('res.partner', string="Client", required=True)
    
    # RPM Questionnaire
    age_bracket = fields.Selection([
        ('18_25', '18-25'),
        ('26_40', '26-40'),
        ('40_60', '40-60'),
        ('60_plus', '60+')
    ], string="Age Group", required=True)
    
    investment_cap = fields.Selection([
        ('low', 'Below 1L'),
        ('mid', '1L - 5L'),
        ('high', '5L - 25L'),
        ('hni', 'Above 25L')
    ], string="Investment Capacity", required=True)
    
    experience_level = fields.Selection([
        ('beginner', 'Beginner (0-1 yr)'),
        ('intermediate', 'Intermediate (1-3 yrs)'),
        ('pro', 'Pro Trader (3+ yrs)')
    ], string="Trading Experience", required=True)
    
    risk_score = fields.Integer(string="Calculated Risk Score", compute='_compute_risk_score', store=True)

    @api.depends('partner_id')
    def _compute_name(self):
        for record in self:
            record.name = f"RPM - {record.partner_id.name}"

    @api.depends('age_bracket', 'investment_cap', 'experience_level')
    def _compute_risk_score(self):
        # Simple logic for now, can be expanded
        scores = {
            'beginner': 10, 'intermediate': 20, 'pro': 30,
            'low': 10, 'mid': 20, 'high': 30, 'hni': 40
        }
        for record in self:
            score = 0
            if record.experience_level:
                score += scores.get(record.experience_level, 0)
            if record.investment_cap:
                score += scores.get(record.investment_cap, 0)
            record.risk_score = score
