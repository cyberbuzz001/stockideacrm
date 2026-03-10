from odoo import models, fields

class CrmLead(models.Model):
    _inherit = 'crm.lead'

    # Lead Enrichment
    demat_id = fields.Char(string="Demat ID")
    demat_status = fields.Selection([
        ('not_opened', 'Not Opened'),
        ('pending', 'Opening'),
        ('active', 'Active')
    ], string="Demat Status", default='not_opened')

    # AI Fields (Phase 4 Prep)
    ai_score = fields.Integer(string="AI Lead Score", help="0-100 likelihood to convert")
    ai_summary = fields.Text(string="AI Summary")
    ai_sentiment = fields.Selection([
        ('positive', 'Positive'),
        ('neutral', 'Neutral'),
        ('negative', 'Negative')
    ], string="Sentiment")

    # NPC Tracking
    npc_count = fields.Integer(string="NPC Count", default=0)
    last_npc_at = fields.Datetime(string="Last NPC Interaction")

    def action_ai_score_lead(self):
        """ AI Logic: Calculates a score 0-100 based on KYC data """
        for lead in self:
            score = 10  # Base Score
            
            # 1. Investment Cap Weight
            if lead.investment_cap == 'hni': score += 40
            elif lead.investment_cap == 'high': score += 30
            elif lead.investment_cap == 'mid': score += 20
            
            # 2. Experience Weight
            if lead.experience_level == 'pro': score += 30
            elif lead.experience_level == 'intermediate': score += 15
            
            # 3. Validation
            if lead.demat_status == 'active': score += 20
            if lead.mobile: score += 5
            
            # Cap at 100
            lead.ai_score = min(score, 100)

    def action_ai_analyze_sentiment(self):
        """ AI Logic: Analyzes description for sentiment keywords """
        for lead in self:
            text = (lead.description or "").lower()
            if any(w in text for w in ['anger', 'upset', 'complain', 'wrong', 'bad']):
                lead.ai_sentiment = 'negative'
            elif any(w in text for w in ['happy', 'good', 'great', 'thanks', 'profit']):
                lead.ai_sentiment = 'positive'
            else:
                lead.ai_sentiment = 'neutral'
                
    def action_ai_draft_email(self):
        """ AI Logic: Drafts a reply (Simulated) """
        self.ensure_one()
        # In a real scenario, this calls OpenAI API
        client_name = self.contact_name or "Client"
        self.ai_summary = f"[AI Draft]\nDear {client_name},\n\nThank you for your interest in our advisory services. based on your profile (Cap: {self.investment_cap}), we recommend our Premium Options Strategy.\n\nBest regards,\n{self.env.user.name}"
