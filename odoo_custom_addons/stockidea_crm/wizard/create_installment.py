from odoo import models, fields, api

class CreateInstallmentWizard(models.TransientModel):
    _name = 'stockidea.installment.wizard'
    _description = 'Create Payment Installment'

    sale_order_id = fields.Many2one('sale.order', string="Sale Order", required=True)
    amount = fields.Float(string="Installment Amount", required=True)
    description = fields.Char(string="Description", default="Installment Payment")

    def action_create_invoice(self):
        """ Creates an invoice for the defined installment amount """
        self.ensure_one()
        # Create Invoice (Account Move) linked to SO
        invoice = self.env['account.move'].create({
            'move_type': 'out_invoice',
            'partner_id': self.sale_order_id.partner_id.id,
            'invoice_origin': self.sale_order_id.name,
            'invoice_line_ids': [(0, 0, {
                'name': self.description,
                'quantity': 1,
                'price_unit': self.amount,
            })],
        })
        return {
            'name': 'Installment Invoice',
            'type': 'ir.actions.act_window',
            'res_model': 'account.move',
            'res_id': invoice.id,
            'view_mode': 'form',
        }
