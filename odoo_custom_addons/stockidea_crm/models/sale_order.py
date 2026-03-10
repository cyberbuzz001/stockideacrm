from odoo import models, fields

class SaleOrder(models.Model):
    _inherit = 'sale.order'

    def action_open_installment_wizard(self):
        """ Opens the wizard to add a partial payment """
        return {
            'name': 'Add Installment',
            'type': 'ir.actions.act_window',
            'res_model': 'stockidea.installment.wizard',
            'view_mode': 'form',
            'target': 'new',
            'context': {'default_sale_order_id': self.id}
        }
