from odoo import http, fields
from odoo.http import request
import datetime

class StockIdeaHeartbeat(http.Controller):
    
    @http.route('/stockidea/heartbeat', type='json', auth='user')
    def heartbeat(self, status, timestamp):
        user = request.env.user
        
        # Lunch Break Logic (13:00 - 13:30)
        # Assuming Server Time is UTC, we need to handle timezone carefully.
        # For simplicity, we'll log the raw ping and let reporting handle the "Lunch Exclusion".
        
        # Log to a custom model or just update the user's last_seen
        # Here we update `res.users`
        
        vals = {
            'last_heartbeat': fields.Datetime.now(),
            'current_status': status
        }
        
        # Use SUPERUSER to write if normal user doesn't have permission to write to own user record (usually they do for preferences, but maybe not fields)
        user.sudo().write(vals)
        
        return {'success': True}
