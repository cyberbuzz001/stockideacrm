from odoo import models, fields

class ResUsers(models.Model):
    _inherit = 'res.users'

    last_heartbeat = fields.Datetime(string="Last Active Pulse")
    current_status = fields.Selection([
        ('active', 'Active'),
        ('idle', 'Idle')
    ], string="Current Status", default='active')
