# Shreesvarn Enterprise AI-Powered CRM

An advanced, high-performance Customer Relationship Management (CRM) system designed for premium advisory services, stock brokerage teams, and high-velocity sales organizations. This CRM blends state-of-the-art aesthetics with robust Laravel backend architectures, real-time communication modules, and AI intelligence to automate client engagement, lead management, and team productivity.

---

## 🌟 Key Features

### 1. Role-Based Bento Command Centers
*   **Admin Dashboard:** Command center featuring live salesperson leaderboards (Today & MTD), real-time revenue trajectory charts (ApexCharts), system health indicators, MTD payments overview, and live system audit logs.
*   **Manager Dashboard:** Unified team management, target assignment, lead allocation metrics, and group performance tracking.
*   **SBA / BA Dashboards:** Agent-centric dashboards showing individual monthly revenue progress bars, urgent follow-up calendars, call queues, and conversion statistics.
*   **Sales Mastery Tips:** Dynamic rotating tips widget loaded from `sales_mastery.json` to keep agents motivated and trained directly on their dashboard.

### 2. Intelligent Lead Management & Allocation
*   **AI Lead Scoring:** Custom machine learning-driven lead scoring command (`php artisan crm:train-lead-scoring`) that analyzes past engagement and historical conversion patterns to predict high-intent prospects.
*   **Auto-Distribution Engine:** Algorithmic lead routing that automatically and fairly distributes incoming leads to online, active sales agents.
*   **Reallocation Tool:** Dynamic manager tool to transfer ownership of single/multiple leads from one salesperson/agent to another with audit trailing.
*   **Bulk & Text Imports:** High-velocity CSV/Excel imports alongside a convenient raw copy-paste bulk text import utility.

### 3. Integrated WhatsApp & Communications Center
*   **Live Chat Tab:** A real-time WhatsApp-style chat interface integrated directly within the Lead details screen.
*   **Meta Cloud API Integration:** Send templates, templates with dynamic parameters, and media directly through the Meta Cloud API.
*   **AI Smart Draft:** Instantly write context-aware replies, reminders, or follow-ups using the embedded AI language generation assistant.
*   **Sentiment & Urgency Classifier:** Automatic categorization of client messages into positive/negative/neutral sentiment and low/medium/high urgency.
*   **Manual Fallback:** Quick-actions to open deep-linked WhatsApp Web messages (`wa.me`) if the API channel is offline.

### 4. Compliance & Document Security
*   **Mandatory Training Middleware:** Restricts access to leads or CRM tools until an employee marks their regulatory/operational compliance training as complete.
*   **Consent Management:** Explicit opt-in and opt-out tracking for client communications.
*   **Secure Document Vault:** Dynamic token-based download security for client identity proofs, KYC forms, and income records.

---

## 🛠️ Technology Stack

| Component | Technology | Description |
| :--- | :--- | :--- |
| **Backend Core** | PHP 8.2+ / Laravel 11 | Enterprise MVC framework handling REST endpoints, DB queries, queues, and CLI commands. |
| **Database** | MySQL 8.0+ | Relational schema with indexing for fast lead filtering, activity logging, and payment tracking. |
| **Real-time Server** | Node.js / Express | Standalone chat microservice located in `/chat-server` for webhook processing. |
| **Process Manager** | PM2 | Manages Node.js servers in production (`ecosystem.config.js`). |
| **Frontend UI** | Tailwind CSS / Alpine.js | Modern responsive interface implementing premium glassmorphism card designs, dark mode utilities, and live interactive state binding. |
| **Data Viz** | ApexCharts | Interactive vector graphs for tracking sales dynamics, conversions, and target metrics. |
| **API Dispatches** | Guzzle HTTP / Meta API | Meta Cloud API wrapper for real-time WhatsApp template delivery. |

---

## 💼 Core Use Cases

### 1. Stock Advisory & Investment Firms
*   **Direct-to-Agent Lead Capture:** Capture inbound leads from Facebook Ads/landing pages, route them instantly via the Auto-Distribution engine, score them using AI, and initiate direct WhatsApp contact in under 60 seconds.
*   **Live Sales Leaderboards:** Gamify the sales floor with live-updating daily and monthly leaderboards showing client payment collections.

### 2. High-Value B2C Sales Teams
*   **Callback Calendar Management:** Keep agents focused on hot prospects with automatic dashboard notifications for scheduled call-backs.
*   **Interactive Disposition Logs:** Log manual call details, outcome categories, and next-action dates with two clicks.

---

## 🔧 Extensibility & Customization

The CRM's modular structure allows for seamless integrations and further development:

### 1. Telephony / VoIP Integrations
*   **Click-to-Call:** Integrate APIs from Twilio, Exotel, or RingCentral to enable one-click outbound calling directly from the browser.
*   **Call Recording Loggers:** Bind call recordings to the Lead's activity timeline.

### 2. Advanced AI Capabilities
*   **Auto-Pilot Chatbot:** Deploy automated AlpineJS qualification flows into real Meta Webhooks to answer common queries (e.g. pricing, brochure requests) before passing the lead to a human agent.
*   **Predictive Churn Engine:** Build machine learning models to highlight leads that are highly likely to request refunds or discontinue services.

### 3. Payment Gateway Syncing
*   **Real-time Invoicing:** Integrate Razorpay, Stripe, or Cashfree webhooks to automatically log payments and close open client balances.
*   **Auto-Renewals Tracker:** Alert agents when subscription plans are nearing their end dates.

---

## 🚀 Installation & Deployment

Refer to the production-ready script and configuration files in the `/deployment` folder:
*   [deploy_vps.sh](file:///D:/2026%20C%20downloads/expertcrmv1/public_html/deployment/deploy_vps.sh) - Automatic deployment script.
*   [docker-compose.yml](file:///D:/2026%20C%20downloads/expertcrmv1/public_html/deployment/docker-compose.yml) - Docker configuration containerizing PHP, Nginx, Node.js, and Redis.
*   [.env.evolution](file:///D:/2026%20C%20downloads/expertcrmv1/public_html/deployment/.env.evolution) - Reference configuration containing all environment variables including Meta Cloud credentials.
