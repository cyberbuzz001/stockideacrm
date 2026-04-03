# CRM Dashboard Feature Overview - COMPLETE
**Expert Stock Advisory CRM System | Role-Based Dashboard Architecture**

---

## 📑 TABLE OF CONTENTS
1. [🔴 Admin Dashboard](#-admin-dashboard)
2. [🟠 Manager Dashboard](#-manager-dashboard)
3. [🟡 SBA/TL Dashboard](#-sbatl-dashboard)
4. [🟢 BA/Business Advisor Dashboard](#-babusiness-advisor-dashboard)
5. [🌐 Common Features (All Roles)](#-common-features-all-roles)
6. [⚙️ System-Wide Features](#️-system-wide-features)
7. [📊 Data Cache Strategy](#-data-cache-strategy)
8. [🔐 Security & Permissions](#-security--permissions)

---

## 🔴 ADMIN DASHBOARD
**The highest-privilege view. Provides a complete bird's-eye view of team performance, revenue, system health, and platform administration.**

### A. Greeting & Period Selector
- **Personalized greeting** with time-of-day emoji (Good Morning 🌅 / Good Afternoon ☀️ / Good Evening 🌙)
- **Admin name & role** display
- **Real-time server status indicator** (✅ All Systems Active / ⚠️ Warnings / 🔴 Critical)
- **Period filter options:** Today / This Week / Month / YTD / Custom Date Range
- **Last sync timestamp** (e.g., "Last updated: 2 minutes ago")

### B. 📊 KPI Strip Cards (5 cards + Quick Stats)

| Card # | Metric | Query | Trend | Click Action |
|--------|--------|-------|-------|--------------|
| 1 | **Today's Free Trials** | Count leads where status='Free Trial' AND created_at=today | ↑↓ vs Yesterday | View all trials → Filtered lead list |
| 2 | **Calling Activity** | Total calls placed across all agents today | ↑↓ vs Yesterday | View call logs → Call analytics page |
| 3 | **Today's Revenue** | Sum of verified payments today (₹) | ↑↓ vs Yesterday | View revenue breakdown → Payment approvals |
| 4 | **Active Agents** | Count agents active in last 5 minutes (pulsing dot) | — | View agent status → Agent management |
| 5 | **Paid Clients** | Total count of all active Paid/Subscribed leads | — | View all clients → Paid client management |

**Additional KPI Quick-Reference Row:**
- Total Leads (all-time)
- Conversion Rate (Today: X%, Month: Y%, All-time: Z%)
- Avg Call Duration (Today)
- Pending Payment Approvals (Count + Amount ₹)
- System Uptime (%)

### C. 📈 Charts Section

| # | Chart Name | Type | Data | Time Range | Interactivity |
|---|------------|------|------|-----------|--------------|
| 1 | **Revenue Trend** | Line/Area Chart | Daily verified revenue | Last 30 days | Hover tooltip with daily breakdown |
| 2 | **Lead Distribution** | Doughnut/Pie Chart | All leads by status (Free Trial, Paid, Follow-up, Cold, etc.) | Current | Click segments to filter leads list |
| 3 | **Calls vs Conversions** | Dual-axis Bar Chart | Daily calls placed vs new Paid conversions | Last 7 days | Hover tooltip, export to CSV |
| 4 | **Team Performance Heatmap** | Heatmap Grid | Agent efficiency score (calls:conversion ratio) by day | Last 30 days | Highlight top/bottom performers |
| 5 | **Payment Pipeline** | Funnel Chart | Leads flowing: Trial → Pending Payment → Paid → Retained | Current | Click to see leads at each stage |

### D. 📋 Data Tables

#### Table 1: Today's New Free Trials
- **Columns:** Lead Name | Mobile | Assigned Agent | Email | Trial Start Date | Trial End Date | Status Badge | Actions (View/Edit/Convert)
- **Sorting:** By time of conversion (newest first)
- **Pagination:** 10 rows per page
- **Actions:** Quick convert to paid, extend trial, send trial reminder

#### Table 2: Top Revenue Agents Today
- **Columns:** Agent Rank (🥇🥈🥉) | Agent Name | Collections Today (₹) | Calls Made | Conversion % | Efficiency Score | Actions (View Profile)
- **Sorting:** By collections (descending)
- **Pagination:** Top 10 agents
- **Highlight:** Color-code top 3 agents in green

#### Table 3: Agent Real-Time Status Dashboard
- **Columns:** Agent Name | Active Leads Count | Assigned Leads | Free Trials | Callbacks Due | Last Activity | Current Status (🟢 Online / 🟡 Away / 🔴 Offline)
- **Real-time Update:** Every 30 seconds
- **Sorting:** By last activity (most recent first)
- **Actions:** Send message, reassign leads, view agent profile

#### Table 4: Pending Payment Approvals (NEW)
- **Columns:** Lead Name | Agent Name | Payment Amount (₹) | Payment Mode | Submitted Date | Status (Pending/Approved/Rejected) | Actions (Approve/Reject/View Details)
- **Sorting:** By submitted date (oldest first)
- **Filtering:** By agent, by amount range
- **Bulk Actions:** Select multiple → Approve All / Reject All

#### Table 5: System Events Log (NEW)
- **Columns:** Timestamp | Event Type | User | Details | Status (Success/Error/Warning)
- **Event Types:** Lead created, Payment processed, Agent login, Backup executed, System alert
- **Sorting:** By timestamp (newest first)
- **Pagination:** 20 rows per page
- **Export:** Download log as CSV/PDF

### E. 📰 Common Feed (Shared Across Roles)

#### Feed Widget 1: 🏆 Leaderboard
- **Title:** "Top 5 Revenue Generators (This Period)"
- **Display:** Bar chart with agent names, revenue, badge (🥇/🥈/🥉)
- **Period:** Match selected period filter
- **Click Action:** View agent profile & detailed stats

#### Feed Widget 2: 📞 Latest Advisory Calls
- **Title:** "Recent Advisory Calls (Last 5)"
- **Display:** Call details → Agent name | Call topic | Participants count | Duration | Timestamp
- **Real-time:** Auto-refresh every 2 minutes
- **Click Action:** Playback recording (if available)

#### Feed Widget 3: 🔴 LIVE Banner
- **Title:** "LIVE Market Call in Progress"
- **Display:** Agent name | Live topic | Participant count | Duration (stopwatch) | Dismiss button
- **Status:** Pulsing red indicator
- **Visibility:** Shows only when a live call is active
- **Click Action:** Join call / View call details

#### Feed Widget 4: ⏰ Today's Follow-ups
- **Title:** "Overdue Follow-ups (All Teams)"
- **Display:** Count + List → Lead Name | Assigned Agent | Days Overdue | Phone | Quick Call button
- **Color Code:** Red if >3 days overdue
- **Sorting:** By days overdue (descending)
- **Click Action:** Open lead details

#### Feed Widget 5: 💳 Pending Approvals
- **Title:** "Payment Approvals Awaiting Admin"
- **Display:** Count + Quick list → Lead | Agent | Amount | Status
- **Urgency Badge:** "URGENT" if >5 items or oldest item >24hrs old
- **Click Action:** Jump to Pending Approvals table

### F. Admin-Exclusive Controls

#### Bulk Management Tools
- **Reassign Leads:** Select multiple leads → reassign to different agent
- **Update Lead Status:** Bulk status changes
- **Send Bulk Messages:** SMS/Email to selected leads/agents

#### User Management Quick Access
- **View/Edit Agents:** Add new agent, deactivate, change permissions
- **View/Edit Managers:** Role assignment, team management
- **Audit Logs:** Track all system changes (who changed what, when)

#### System Configuration Panel
- **Database Backup Status:** Last backup timestamp, size, backup schedule
- **Integration Status:** Check connected services (SMS provider, payment gateway, etc.)
- **Email Configuration:** Test email delivery, view email queue
- **API Key Management:** View, regenerate, revoke API keys

#### Report Generation
- **Custom Reports:** Select metrics + date range → generate PDF/Excel
- **Scheduled Reports:** Automatic daily/weekly reports sent to email
- **Export Data:** Export leads, payments, call logs as CSV/Excel

---

## 🟠 MANAGER DASHBOARD
**Focused on team oversight, revenue reporting, and escalation management — without agent-level lead access.**

### A. Greeting & Period Selector
- **Personalized greeting** with context (e.g., "Welcome back, Raj! You have 3 escalations pending")
- **Period filter:** Today / This Week / Month / YTD / Custom Date Range
- **Team selector** (if manager oversees multiple teams)
- **Quick stats banner:** Team size, total leads under management

### B. 📊 KPI Cards (4 cards + Extended Metrics)

| Card # | Metric | Query | Trend |
|--------|--------|-------|-------|
| 1 | **Team Members** | Count active agents in manager's team | — |
| 2 | **Period Revenue** | Sum verified payments from team for selected period | ↑↓ vs Previous Period |
| 3 | **Running Free Trials** | Count active Free Trials assigned to team agents | — |
| 4 | **Pending Payments** | Count pending payment approvals in team | Alert badge if >5 |

**Additional Metrics Row:**
- Total Team Leads
- Avg Revenue per Agent
- Team Conversion Rate
- Team Calls Made (Period)
- Escalations Pending (Count)

### C. 📈 Charts Section

| # | Chart Name | Type | Data |
|---|------------|------|------|
| 1 | **Team Revenue Trend** | Line Chart | Daily team revenue (last 30 days) |
| 2 | **Agent Performance Comparison** | Bar Chart | Each agent's revenue + calls (this period) |
| 3 | **Team Lead Status Distribution** | Doughnut Chart | Team leads by status (Free Trial, Paid, Follow-up, etc.) |
| 4 | **Call Volume Trend** | Area Chart | Team calls per day (last 7 days) |

### D. 📋 Data Tables

#### Table 1: Agent Performance Table
- **Columns:** Agent Name | Active Leads | Free Trials | Calls (Period) | Revenue (₹) | Conversion % | Efficiency Score
- **Sorting:** By revenue (descending)
- **Color Code:** Top performers in green, underperformers in orange
- **Actions:** View profile, send performance alert, reassign leads

#### Table 2: Pending Approvals
- **Columns:** Lead Name | Agent Name | Amount (₹) | Payment Mode | Submitted Date | Actions (Approve/Reject)
- **Sorting:** By submitted date (oldest first)
- **Bulk Actions:** Approve/Reject multiple at once
- **Alert:** Highlight items >24 hours old

#### Table 3: Escalated Leads
- **Columns:** Lead Name | Agent Name | Escalation Reason | Priority (High/Medium/Low) | Escalation Date | Status | Actions (View/Resolve)
- **Sorting:** By priority, then by date
- **Filter:** By escalation reason
- **Quick Resolve Button:** Mark as resolved

#### Table 4: Team's Overdue Follow-ups
- **Columns:** Lead Name | Agent Name | Days Overdue | Phone | Next Action | Status
- **Sorting:** By days overdue (highest first)
- **Color Code:** Red if >3 days
- **Quick Action:** Reassign to another agent, send reminder

### E. 📰 Common Feed (Shared Across Roles)

#### Feed Widget 1: 🏆 Leaderboard
- **Title:** "Top 5 Agents (This Period)"
- **Display:** Bar chart with agent names, revenue, rank badges
- **Period:** Match selected period filter

#### Feed Widget 2: 📞 Latest Advisory Calls
- **Title:** "Recent Advisory Calls (Last 5)"
- **Display:** Agent | Call Topic | Participants | Duration | Timestamp

#### Feed Widget 3: 🔴 LIVE Market Call Banner
- **Title:** "LIVE Market Call Active"
- **Display:** Topic | Participants | Duration

#### Feed Widget 4: 📈 Revenue Target Progress Bar
- **Title:** "Monthly Revenue Target: ₹25,00,000"
- **Display:** Visual progress bar → Current: ₹18,50,000 (74% of target)
- **Breakdown:** Days remaining, daily target pace indicator

### F. Manager-Exclusive Controls

#### Team Management
- **Reassign Leads:** Redistribute leads among team members
- **Adjust Targets:** Set individual agent targets
- **Performance Alerts:** Send individual/group alerts to agents

#### Approval Workflows
- **Batch Approvals:** Review + approve multiple payments at once
- **Escalation Resolution:** Mark escalations as resolved
- **Lead Status Updates:** Bulk update team lead statuses

#### Team Reports
- **Performance Report:** Agent-wise performance (PDF)
- **Revenue Report:** Team revenue by period
- **Escalation Report:** All escalations handled this period

---

## 🟡 SBA/TL DASHBOARD
**A hybrid dashboard combining team oversight with personal lead management. The most feature-rich role view.**

### A. Greeting & Period Selector
- **Personalized greeting** with time-of-day context (e.g., "Good Morning, Priya! 🌅")
- **SBA name & team info** (Team lead of X agents)
- **Period filter:** Today / This Week / Month / YTD / Custom Date Range
- **Quick links:** Jump to team → Jump to personal leads

### B. 📊 KPI Cards (5 cards for Team Metrics)

| Card # | Metric | Scope | Trend |
|--------|--------|-------|-------|
| 1 | **Team Leads** | Total leads assigned across SBA's team | — |
| 2 | **Team Calls Today** | Total calls made by all team members | ↑↓ vs Yesterday |
| 3 | **Team Free Trials** | Active Free Trials across team | — |
| 4 | **Team Revenue** | Verified revenue from team (period) | ↑↓ vs Previous Period |
| 5 | **Team Conversion Rate** | Converted leads / Total leads (%) | — |

**Additional Team Metrics:**
- Avg calls per agent
- Avg revenue per agent
- Pending approvals (team)

### C. 📈 Charts Section (Team Focused)

| # | Chart Name | Type | Data |
|---|------------|------|------|
| 1 | **Team Call Volume** | Bar Chart | Daily calling activity (7 days) |
| 2 | **Team Revenue Trend** | Line Chart | Daily team revenue (30 days) |
| 3 | **Agent Contribution** | Pie Chart | Revenue breakdown by agent |
| 4 | **Team Lead Status** | Doughnut Chart | Team leads by status |

### D. 📋 Data Tables (Team-Focused)

#### Table 1: Team Performance Table
- **Columns:** Agent Name | Active Leads | Calls (Today) | Revenue (Period) | Conversion % | Status (🟢/🟡/🔴)
- **Sorting:** By revenue (descending)
- **Actions:** View profile, send performance feedback, reassign leads

#### Table 2: Team's Overdue Follow-ups
- **Columns:** Lead Name | Assigned Agent | Days Overdue | Phone | Status
- **Sorting:** By days overdue (highest first)
- **Color Code:** Red if overdue
- **Bulk Reassign:** Select multiple → reassign to different agent

### E. My Personal Dashboard Section (SBA's Own Leads)

#### 📊 Personal KPI Cards (5 cards)

| Card # | Metric | Trend |
|--------|--------|-------|
| 1 | **My Total Leads** | All personal leads currently assigned | — |
| 2 | **My Calls Today** | Calls I made today | ↑↓ vs Yesterday |
| 3 | **My Free Trials** | My active Free Trial leads | — |
| 4 | **My Revenue (Period)** | My verified revenue | ↑↓ vs Previous Period |
| 5 | **My Follow-ups Due** | Overdue callbacks/follow-ups | 🔴 Alert badge |

#### 📈 Personal Charts

| # | Chart | Type | Data |
|---|-------|------|------|
| 1 | **My Call Volume** | Bar Chart | My daily calls (7 days) |
| 2 | **My Conversion Funnel** | Doughnut Chart | My leads by status |

#### 📋 Personal Tables

##### Table 1: My Priority Queue
- **Columns:** Lead Name | Status | Last Contact | Days Since Contact | Next Action | Phone
- **Sorting:** By urgency → Overdue Follow-ups → New Today → Cold Leads → Trials
- **Limit:** Show top 15 leads
- **Actions:** Make call, update status, log interaction

##### Table 2: My Overdue Follow-ups
- **Columns:** Lead Name | Days Overdue | Phone | Assigned Date | Actions
- **Sorting:** By days overdue (highest first)
- **Color Code:** Red badges for >3 days overdue
- **Quick Action:** Call button (tel: link)

##### Table 3: My Active Free Trials
- **Columns:** Lead Name | Trial Start | Trial End | Days Remaining | Status
- **Sorting:** By days remaining (ascending - urgent first)
- **Alerts:** Orange badge if <5 days remaining, Red if <2 days
- **Actions:** Send trial reminder, extend trial, convert to paid

##### Table 4: My Paid Clients
- **Columns:** Client Name | Subscription Status | Monthly Revenue | Renewal Date | Last Payment | Actions
- **Sorting:** By renewal date (ascending - urgent first)
- **Color Code:** Green for active, yellow for expiring soon
- **Actions:** View details, renew subscription, send renewal reminder

### F. 🔔 Alerts & Notifications

#### Alert 1: Pending Training
- **Banner:** "⚠️ Complete mandatory training module '[Module Name]' by [Date]"
- **Action Button:** "Start Training"

#### Alert 2: Team Performance Alert
- **Condition:** If any team member is underperforming (below target)
- **Display:** "Agent [Name] is 20% below target. View details"

#### Alert 3: Renewal Alert
- **Condition:** Paid clients with renewal <3 days away
- **Display:** Count of renewals + list

### G. 📰 Common Feed (Shared Across Roles)

#### Feed Widget 1: 🏆 Leaderboard
- **Title:** "Top 5 Agents (This Period)"
- **Display:** Bar chart with ranks
- **Highlight:** SBA's own rank relative to peers

#### Feed Widget 2: 📞 Latest Advisory Calls
- **Title:** "Recent Advisory Calls"
- **Display:** Last 5 calls

#### Feed Widget 3: 🔴 LIVE Market Call Banner
- **Title:** "LIVE Market Call Active"

#### Feed Widget 4: 📈 Revenue Target Progress Bar
- **Title:** "My Monthly Target: ₹[Amount]"
- **Display:** Personal revenue vs target
- **Progress:** X% achieved, Y% remaining, Z days left

### H. SBA-Exclusive Controls

#### Lead Reassignment
- **Reassign own leads to team members**
- **Reassign underperforming agent's leads to another team member**

#### Performance Coaching
- **Send performance feedback to team members**
- **Set individual targets for agents**

#### Team Reports
- **Generate team performance report**
- **Export team data**

---

## 🟢 BA/BUSINESS ADVISOR DASHBOARD
**Personal productivity dashboard. Focused entirely on agent's own assigned leads, calls, revenue, and targets.**

### A. Greeting & Period Selector
- **Personalized greeting** with time-of-day context (e.g., "Good Evening, Arjun! 🌙")
- **BA name & stats summary** (Total leads: X, Active trials: Y, Paid clients: Z)
- **Period filter:** Today / This Week / Month / YTD / Custom Date Range
- **Quick action bar:** "Make a Call" | "Add Lead" | "Log Interaction"

### B. 📊 KPI Cards (6 cards - All Personal Metrics)

| Card # | Metric | Query/Filter | Trend |
|--------|--------|--------------|-------|
| 1 | **Total Assigned Leads** | All leads where agent_id = Auth::id() | — |
| 2 | **New Leads (Period)** | Leads assigned to me within selected period | ↑↓ vs Previous Period |
| 3 | **Calls Today** | Calls I made today | ↑↓ vs Yesterday |
| 4 | **Follow-ups Due** | Overdue Call Back / Follow Up leads (callback_date <= today) | 🔴 Alert badge |
| 5 | **Active Trials** | Free Trial leads with trial_end_date >= today | ⏰ Warning badge if <5 days |
| 6 | **Paid Clients** | Total converted Paid/Subscribed clients | — |

**Additional Personal Metrics Row:**
- Call Duration (Today, total minutes)
- Conversion Rate (This period, %)
- Avg Lead Age (days)
- Revenue (This period, ₹)

### C. 📈 Charts Section (Personal Performance)

| # | Chart Name | Type | Data | Interactivity |
|---|------------|------|------|----------------|
| 1 | **My Call Volume** | Bar Chart | Daily calls past 7 days | Hover tooltip with details |
| 2 | **My Conversion Funnel** | Doughnut Chart | My leads by status (Free Trial, Paid, Follow-up, Cold, etc.) | Click segments to filter leads |
| 3 | **My Revenue Trend** | Line Chart | Daily revenue (past 30 days) | Hover for daily breakdown |
| 4 | **Today's Call Timeline** | Horizontal Timeline | Calls made today with time + duration | Shows call progression throughout day |

### D. 📋 Data Tables & Lists (All Personal)

#### Table 1: My Priority Queue (MOST IMPORTANT)
- **Title:** "My Priority Queue - Action Required"
- **Columns:** Lead Name | Phone | Last Contact | Days Since Contact | Next Action | Priority Indicator | Actions (Call/Update/View)
- **Sorting Order (Strict Priority):**
  1. Overdue Follow-ups (red, >0 days past due)
  2. Follow-ups Due Today (orange, today)
  3. New Leads (today) (blue)
  4. Cold Leads (not contacted in >7 days) (yellow)
  5. Active Trials (green)
- **Limit:** Show top 15 leads (paginate after 15)
- **Quick Actions:** 
  - "📞 Call" → Dial number (tel: link on mobile, click-to-dial if integrated)
  - "✏️ Update" → Change status/next action
  - "🔍 View" → Full lead details
- **Color Coding:**
  - 🔴 Red row: Overdue >3 days
  - 🟠 Orange row: Due today
  - 🔵 Blue row: New today
  - 🟡 Yellow row: Cold leads
  - 🟢 Green row: Active trials

#### Table 2: Upcoming Follow-ups
- **Title:** "My Follow-ups (Next 5 Days)"
- **Columns:** Lead Name | Phone | Scheduled Date & Time | Days Away | Status
- **Sorting:** By scheduled date (ascending)
- **Limit:** Show next 5 scheduled follow-ups
- **Actions:** Mark complete, reschedule, call now

#### Table 3: Today's Overdue Follow-ups (Detailed)
- **Title:** "⏰ Overdue Follow-ups (Today)"
- **Columns:** Lead Name | Phone | Days Overdue | Last Contact Date | Last Action | Status
- **Sorting:** By days overdue (highest first)
- **Limit:** Show up to 10
- **Color Code:** Red for >3 days, orange for 1-3 days
- **Quick Actions:** Call button, mark as completed, reschedule

#### Table 4: Active Free Trials
- **Title:** "🎯 My Active Free Trials"
- **Columns:** Lead Name | Phone | Trial Start Date | Trial End Date | Days Remaining | Status
- **Sorting:** By days remaining (ascending - urgent first)
- **Limit:** Show up to 10
- **Color Code:** 
  - 🔴 Red if <2 days remaining
  - 🟠 Orange if 2-5 days remaining
  - 🟢 Green if >5 days remaining
- **Actions:** 
  - "Send Trial Reminder" (SMS/Email)
  - "Extend Trial" (extend by X days)
  - "Convert to Paid" (initiate payment)
  - "View Lead Details"

#### Table 5: My Paid Clients List
- **Title:** "💰 My Paid Clients"
- **Columns:** Client Name | Phone | Subscription Type | Monthly Revenue (₹) | Renewal Date | Days Until Renewal | Last Payment Date | Status
- **Sorting:** By renewal date (ascending - urgent first)
- **Limit:** Show up to 10
- **Color Code:**
  - 🟢 Green: Active, renewal >30 days away
  - 🟠 Orange: Renewal 5-30 days away
  - 🔴 Red: Renewal <5 days away or overdue
- **Actions:**
  - "View Details" (client info + payment history)
  - "Send Renewal Reminder" (SMS/Email)
  - "Process Renewal" (initiate payment)
  - "View Payment History"

#### Table 6: My Cold Leads (Not Contacted in 7+ Days)
- **Title:** "🧊 Cold Leads (Not Contacted in 7+ Days)"
- **Columns:** Lead Name | Phone | Last Contact Date | Days Since Contact | Status | Actions
- **Sorting:** By days since contact (highest first)
- **Limit:** Show up to 10
- **Quick Action:** Call, send message, reassign

#### Table 7: Call Logs (Today)
- **Title:** "📞 My Calls Today"
- **Columns:** Lead Name | Call Time | Duration | Outcome (Connected/Not answered/Voicemail/Declined) | Notes
- **Sorting:** By time (newest first)
- **Real-time:** Auto-refresh every 2 minutes

### E. 🔔 Alerts & Notifications

#### Alert 1: Pending Training
- **Display:** Banner → "⚠️ Complete mandatory training module '[Module Name]' by [Date]"
- **Action Button:** "Start Training Now"

#### Alert 2: Renewal Alert
- **Display:** "💳 3 Paid clients expiring within 3 days"
- **Count Badge:** Show count of expiring renewals
- **List:** Quick list of expiring clients

#### Alert 3: Call Target Alert (NEW)
- **Condition:** If daily call count <50% of daily target (e.g., target 20 calls/day, made <10 by 3 PM)
- **Display:** "⏰ You're behind on daily calls. Target: 20 | Made: 8"
- **Dismissible:** Can dismiss for rest of day

#### Alert 4: Follow-up Due Alert (NEW)
- **Condition:** Follow-up callback is due in <5 minutes
- **Display:** Toast notification → "Lead [Name] follow-up due in 3 minutes"
- **Action:** Quick call button

#### Alert 5: Conversion Alert (NEW)
- **Condition:** Trial expiring in <24 hours not yet converted
- **Display:** "⚠️ [Lead Name] trial expires tomorrow. Convert to paid?"
- **Action Button:** "Convert Now"

### F. 📰 Common Feed (Shared Across Roles)

#### Feed Widget 1: 🏆 Leaderboard
- **Title:** "Top 5 Agents (This Period)"
- **Display:** Bar chart with agent names, revenue, rank badges
- **Highlight:** BA's own position relative to peers (e.g., "🥇 You're #3 this week!")

#### Feed Widget 2: 📞 Latest Advisory Calls
- **Title:** "Recent Advisory Calls (Last 5)"
- **Display:** Advisor name | Call topic | Participants | Duration | Timestamp
- **Real-time:** Auto-refresh every 2 minutes

#### Feed Widget 3: 🔴 LIVE Market Call Banner
- **Title:** "🔴 LIVE Market Call in Progress"
- **Display:** Topic | Participants count | Duration (stopwatch) | "Join" button (if applicable) | Dismiss button
- **Auto-dismiss:** Hides when call ends

#### Feed Widget 4: 📈 Revenue Target Progress Bar
- **Title:** "My Monthly Target: ₹[Personal Target Amount]"
- **Display:**
  - Visual progress bar → Current: ₹[Amount] / Target: ₹[Target] (X% achieved)
  - Days remaining in month
  - Daily pace indicator ("You need ₹[X] per day to hit target")
  - Trend arrow (↑ on track / ↓ behind / — neutral)
- **Detailed View Button:** Click to see revenue breakdown by day

### G. BA-Exclusive Controls

#### Lead Management
- **Quick Add Lead:** Fast-entry form to add new lead
- **Update Lead Status:** Bulk status updates
- **Delete/Archive Lead:** Archive old cold leads
- **Add Note:** Internal notes on lead

#### Call Logging
- **Log Call:** Record new call (lead, duration, outcome, notes)
- **Reschedule Callback:** Schedule next follow-up
- **Send Message:** Send SMS/Email to lead

#### Personal Settings
- **Change Daily Target:** Modify personal call/revenue targets
- **Set Notification Preferences:** Alert frequency, notification channels
- **Download Reports:** Export my performance data

---

## 🌐 COMMON FEATURES (ALL ROLES)

### 1. 🔴 LIVE Market Call Banner
- **Display Location:** Fixed top banner (persistent across all pages)
- **Content:** 
  - Pulsing 🔴 indicator
  - "LIVE Advisory Call in Progress"
  - Current speaker/advisor name
  - Call topic
  - Participant count
  - Duration (stopwatch timer)
  - Dismiss button (X)
  - "Join Call" button (if user can join)
- **Visibility Rules:**
  - Only shows when a live call is active
  - Auto-hides when call ends
  - Can be dismissed by user (re-appears when new call starts)
- **Real-time:** WebSocket/Pusher integration for instant updates
- **Audio:** Optional notification sound on new call start (user can disable)

### 2. 🏆 Leaderboard Widget
- **Title:** "Top 5 Revenue Generators (This Period)"
- **Display Format:** 
  - Bar chart with agent names (Y-axis) and revenue (X-axis)
  - Rank badges: 🥇 🥈 🥉 #4 #5
  - Revenue amount in ₹
- **Period Selector:** Match dashboard period filter (Today/Week/Month/YTD)
- **Highlight Rules:**
  - User's own position (if applicable) highlighted in different color
  - Gold background for #1, silver for #2, bronze for #3
- **Interactivity:** Click on agent → view their detailed profile
- **Refresh:** Updates every 5 minutes

### 3. 📞 Latest Advisory Calls Feed
- **Title:** "Recent Advisory Calls"
- **Display Format:** Vertical list of last 5 calls
- **Per Item:**
  - Advisor/Agent name
  - Call topic (title/description)
  - Participants count (X attendees)
  - Call duration
  - Timestamp (e.g., "2 minutes ago")
  - View/Replay button (if recording available)
- **Sorting:** By timestamp (newest first)
- **Real-time:** Auto-refresh every 2 minutes
- **Pagination:** Show 5, load more option available

### 4. 📈 Revenue Target Progress Bar
- **Title:** "[Role Name]'s Revenue Target"
- **Display:**
  - Horizontal progress bar (visual %)
  - Current achieved amount (₹)
  - Target amount (₹)
  - Percentage complete (X%)
  - Days remaining in period
- **Breakdown (on hover/click):**
  - Daily breakdown for remaining days
  - Daily pace needed to hit target
  - Trend (↑ on track / ↓ behind / — neutral)
- **Color Coding:**
  - 🟢 Green: On track or exceeded (>100% expected pace)
  - 🟡 Yellow: Behind but recoverable (70-100% expected pace)
  - 🔴 Red: Significantly behind (<70% expected pace)
- **Refresh:** Updates every 30 minutes

### 5. 📞 Follow-up Notifications (Real-time Check-in)
- **Type:** Toast notification (bottom-right corner)
- **Trigger:** When follow-up callback is due within 5 minutes
- **Content:** "Lead [Name] callback due in X minutes"
- **Actions:** [Call Now] [Snooze] [Dismiss]
- **Sound:** Optional notification sound (user can disable in settings)
- **Frequency:** Max 1 notification per follow-up

### 6. ⏰ Today's Follow-ups Panel
- **Title:** "Today's Follow-ups"
- **Display Location:** Sidebar or collapsible panel on dashboard
- **Content:**
  - Count of overdue follow-ups
  - Count of today's follow-ups
  - Quick list (5 most urgent)
  - Per item: Lead name | Phone | Days overdue | Quick call button
- **Sorting:** By urgency (overdue > today > future)
- **Color Coding:** Red for overdue, orange for today
- **Refresh:** Every 5 minutes
- **Role-Scoped:** Shows only user's own follow-ups (agent) or team's (manager/SBA)

### 7. 🔍 Global Search Header
- **Location:** Top navigation bar (persistent across all pages)
- **Functionality:** 
  - Real-time search as user types
  - Search by: Lead name, Mobile number, Email, Agent name, Lead status
- **Results Display:**
  - Dropdown showing up to 10 results
  - Each result shows: Lead name | Phone | Status | Assigned agent | Last contact
  - Click result → jump to lead details page
- **Keyboard Shortcut:** Cmd+K (Mac) or Ctrl+K (Windows) to focus search
- **Clear Results:** X button to clear search field

### 8. 📱 Mobile Responsive Design
- **Breakpoints:** 
  - Desktop (1024px+): Full layout
  - Tablet (768px-1023px): 2-column layout, collapsed nav
  - Mobile (<768px): Single column, hamburger nav
- **Mobile-Specific Features:**
  - Larger touch targets (min 44px height)
  - Full-width cards
  - Collapsible sections to save screen space
  - "Call" buttons are `tel:` links for direct dialing
  - Sticky action buttons at bottom (Make Call, Add Lead, etc.)

### 9. 🎨 Theme/Dark Mode Toggle (NEW)
- **Location:** Settings menu or header toggle
- **Options:** Light / Dark / Auto (matches system preference)
- **Persistence:** Save user preference in database
- **Visual Changes:**
  - Light mode: White backgrounds, dark text
  - Dark mode: Dark backgrounds, light text, reduced eye strain
  - Chart colors adjusted for visibility in both modes

### 10. 🔔 Notification Center (NEW)
- **Location:** Bell icon in header
- **Types of Notifications:**
  - Payment approvals needed
  - Follow-up reminders
  - System alerts
  - Message from manager
  - Lead assignment changes
- **Features:**
  - Mark as read/unread
  - Filter by type
  - Delete notifications
  - Settings: Choose which notifications to receive

### 11. 🕐 Time Zone Support (NEW)
- **Feature:** Display all times in user's local time zone
- **Settings:** Allow user to set preferred time zone
- **Implementation:** Store all times in UTC, convert on display
- **Affected Elements:** Timestamps, scheduled dates, follow-up times

---

## ⚙️ SYSTEM-WIDE FEATURES

### A. 🔐 Role-Based Access Control (RBAC)
- **Roles:** Admin, Manager, SBA/TL, BA/Agent
- **Permissions:** Each role has specific dashboard elements they can access
- **Rules:**
  - Admin: Full access to all dashboards and system controls
  - Manager: Only see their team's data (not other managers' teams)
  - SBA/TL: See team + own data
  - BA: Only see their own data
- **Enforcement:** Check permissions on backend before loading data

### B. 🔐 Data Security & Audit Logging
- **Audit Trails:** Log all data access and modifications
  - Who accessed what data, when
  - IP address of accessor
  - What changes were made (old value → new value)
  - Timestamp with timezone
- **Data Encryption:** Sensitive data (payments, phone numbers) encrypted at rest
- **Session Management:** Automatic logout after 30 minutes of inactivity (configurable)
- **API Rate Limiting:** Prevent abuse (e.g., max 100 requests per minute per user)

### C. 🔄 Real-Time Updates (WebSocket/Pusher)
- **Channels:**
  - Admin channel: All system events
  - Team channel: Team-specific updates (for managers/SBA)
  - Personal channel: Personal lead/call updates
- **Events Broadcast:**
  - New lead created
  - Lead status changed
  - Payment processed
  - Call logged
  - Agent online/offline status
  - New message from manager
- **Latency:** <2 second update delay target

### D. 📊 Advanced Filtering & Search
- **Lead Filters:**
  - By status (Free Trial, Paid, Follow-up, Cold, etc.)
  - By date range (creation, last contact, next follow-up)
  - By value (revenue, expected value range)
  - By agent (assigned agent)
  - By source (how lead was acquired)
- **Date Range Picker:** Calendar widget for precise date selection
- **Saved Filters:** Save commonly used filter combinations

### E. 📥 Export & Download Features
- **Export Formats:** CSV, Excel (.xlsx), PDF
- **Exportable Data:**
  - Lead lists (with all filters applied)
  - Call logs
  - Payment records
  - Performance reports
  - Custom reports
- **Scheduled Exports:** Automatic daily/weekly reports sent to email

### F. 📧 Email & SMS Notifications
- **Email Notifications Sent For:**
  - Payment approval needed (to admin/manager)
  - Lead assigned to agent
  - Performance alerts
  - Daily/weekly reports (if subscribed)
  - Trial about to expire reminders
- **SMS Notifications Sent For:**
  - Follow-up due reminders (to agent)
  - Trial conversion urgency alerts
  - Critical system alerts
- **User Control:** Disable/customize notification frequency in settings

### G. 🎯 Performance Analytics (NEW)
- **Agent Analytics:**
  - Calls per day (trend)
  - Conversion rate (trend)
  - Avg time to conversion
  - Customer lifetime value (CLV)
  - Churn rate (paid clients lost)
- **Team Analytics:**
  - Team growth (new leads per week)
  - Revenue trend
  - Efficiency metrics
- **Export:** Generate detailed performance reports (PDF)

### H. 🤖 Automated Actions (NEW)
- **Auto-Escalate:** If lead overdue >7 days, automatically escalate to manager
- **Auto-Reminder:** Send SMS reminder to agent 1 hour before follow-up
- **Auto-Convert:** If trial accepted, auto-generate payment request (admin approval needed)
- **Auto-Archive:** Archive leads with no activity >90 days (with warning first)

### I. 💬 In-App Messaging (NEW)
- **Feature:** Send messages between users
- **Types:**
  - Manager → Agent (performance feedback, instructions)
  - Agent → Agent (handoff notes, collaboration)
  - Agent → Admin (escalation notes, questions)
- **Display:** Message badge in header, full chat history accessible
- **Notifications:** Toast notification + email for new messages

### J. 📞 Call Recording & Playback (NEW - If Integrated)
- **Feature:** Record calls and save recording links
- **Access:** Only admin/manager/assigned agent can access
- **Storage:** Cloud storage (AWS S3, Google Cloud, etc.)
- **Playback:** In-app player with timestamp notes
- **Compliance:** Ensure recording complies with local telecom laws

### K. 📱 Mobile App Push Notifications (NEW)
- **For Mobile Users:** Push notifications on iOS/Android
- **Events:**
  - New lead assigned
  - Follow-up due reminders
  - Call from manager
  - Payment processed
  - Trial expiring soon
- **Sound & Badge:** Customizable sound, badge count updates

### L. 🔄 Data Sync & Offline Mode (NEW - Optional)
- **Offline Capability:** View recently accessed leads/calls when offline
- **Auto-Sync:** When connection restored, sync changes to server
- **Conflicts:** If data changed on server during offline period, show conflict resolution UI

---

## 📊 DATA CACHE STRATEGY

### Cache Duration by Role

| Role | Cache Duration | Reason |
|------|----------------|--------|
| **Admin** | 5 minutes | Admin needs near real-time overview of all system activity |
| **Manager** | 15 minutes | Manager's data is less volatile; team changes don't happen frequently |
| **SBA/TL** | 10 minutes | SBA sees both team + personal data; needs balance of real-time + performance |
| **BA/Agent** | 3 minutes | Agents need frequent real-time updates on their leads + daily targets |

### Cache Invalidation Rules
- **Cache is purged immediately when:**
  - New lead assigned to agent
  - Lead status changed
  - Payment processed
  - Agent online/offline status changes
  - Call logged

- **Cache is purged on schedule (every 5 min) for:**
  - KPI metrics (revenue, calls, trials)
  - Leaderboard rankings
  - Agent online status

### Cache Storage Backend
- **Recommended:** Redis (fast, in-memory, supports TTL)
- **Fallback:** File-based or database caching
- **Laravel Implementation:** Use `Cache::remember()` with TTL

### Example Implementation
```php
// Admin dashboard metrics - 5 minute cache
Cache::remember('admin_metrics', 300, function() {
    return [
        'trials_today' => Lead::where('status', 'Free Trial')->whereDate('created_at', today())->count(),
        'revenue_today' => Payment::where('status', 'completed')->whereDate('created_at', today())->sum('amount'),
        'active_agents' => User::where('role', 'agent')->where('last_activity_at', '>=', now()->subMinutes(5))->count(),
    ];
});

// Agent dashboard metrics - 3 minute cache
Cache::remember('agent_metrics:' . Auth::id(), 180, function() {
    return [
        'my_leads' => Lead::where('agent_id', Auth::id())->count(),
        'my_trials' => Lead::where('agent_id', Auth::id())->where('status', 'Free Trial')->count(),
        'my_paid' => Lead::where('agent_id', Auth::id())->whereIn('status', ['Paid', 'Subscribed'])->count(),
    ];
});
```

---

## 🔐 SECURITY & PERMISSIONS

### Authentication
- **Method:** Laravel Auth with session-based authentication
- **MFA:** Optional but recommended (TOTP or SMS-based)
- **Login Tracking:** Log all login attempts (IP, timestamp, success/failure)
- **Suspicious Activity:** Alert admin if unusual login pattern detected (e.g., login from different country)

### Authorization (Who Can See What)

| Action | Admin | Manager | SBA | BA |
|--------|-------|---------|-----|-----|
| View all dashboards | ✅ | ❌ | ❌ | ❌ |
| View own dashboard | ✅ | ✅ | ✅ | ✅ |
| View team dashboard | ✅ | ✅ (own team) | ✅ (own team) | ❌ |
| View all leads | ✅ | ✅ (team only) | ✅ (team + personal) | ✅ (personal only) |
| Approve payments | ✅ | ✅ (team only) | ❌ | ❌ |
| Manage users | ✅ | ❌ | ❌ | ❌ |
| Edit own settings | ✅ | ✅ | ✅ | ✅ |

### Data Access Rules
- **Admin:** Can access all data without restriction
- **Manager:** Can access:
  - Own profile data
  - Team members' data (agents they manage)
  - Team's leads, payments, calls
  - Cannot access other managers' data
- **SBA/TL:** Can access:
  - Own profile data
  - Team members' data (agents they manage)
  - Team's leads, payments, calls
  - Own personal leads
  - Cannot access other teams' data or other SBAs' personal data
- **BA:** Can access:
  - Own profile data
  - Own leads only
  - Own call logs only
  - Cannot access other agents' data
  - Cannot access manager/team analytics

### Query-Level Security
- **Always filter by user/role:** 
  ```php
  // BA can only see own leads
  $leads = Lead::where('agent_id', Auth::id())->get();
  
  // Manager can see team leads
  $leads = Lead::whereIn('agent_id', Auth::user()->team->agent_ids)->get();
  
  // Admin sees all
  $leads = Lead::all();
  ```

- **Middleware for protecting routes:**
  ```php
  Route::middleware(['auth', 'role:admin'])->group(function() {
      Route::get('/admin/dashboard', 'AdminController@index');
  });
  ```

---

## 📋 SUMMARY TABLE: Features by Role

| Feature | Admin | Manager | SBA | BA |
|---------|-------|---------|-----|-----|
| Personal KPI Cards | ✅ | ✅ | ✅ | ✅ |
| Team KPI Cards | ✅ | ✅ | ✅ | ❌ |
| Personal Charts | ✅ | ✅ | ✅ | ✅ |
| Team Charts | ✅ | ✅ | ✅ | ❌ |
| Personal Lead Tables | ✅ | ✅ | ✅ | ✅ |
| Team Lead Tables | ✅ | ✅ | ✅ | ❌ |
| Leaderboard | ✅ | ✅ | ✅ | ✅ |
| Latest Calls Feed | ✅ | ✅ | ✅ | ✅ |
| LIVE Call Banner | ✅ | ✅ | ✅ | ✅ |
| Target Progress Bar | ✅ | ✅ | ✅ | ✅ |
| Follow-up Notifications | ✅ | ✅ | ✅ | ✅ |
| User Management | ✅ | ❌ | ❌ | ❌ |
| Payment Approvals | ✅ | ✅ (team) | ❌ | ❌ |
| Reports Generator | ✅ | ✅ | ✅ | ✅ |
| Export Data | ✅ | ✅ | ✅ | ✅ |
| Settings/Preferences | ✅ | ✅ | ✅ | ✅ |

---

## 📈 UI/UX Standards

### Color Scheme (Tailwind-Based)
- **Primary Brand:** `blue-600`
- **Success/Revenue:** `green-500`
- **Warning/Overdue:** `amber-500`
- **Danger/Critical:** `red-500`
- **Neutral/Dividers:** `slate-300` / `slate-400`
- **Light Background:** `slate-50`
- **Dark Background:** `slate-900`

### Typography
- **Headings:** Bold, sans-serif, hierarchical sizing
  - H1 (Page title): 32px
  - H2 (Section title): 24px
  - H3 (Subsection): 20px
  - H4 (Card title): 16px
- **Body Text:** 14-16px, line-height 1.5
- **Numbers/Metrics:** Larger font, bold color (primary brand color)

### Spacing
- **Grid Padding:** 16px (base), 24px (large), 8px (small)
- **Card Margins:** 16px vertical, 24px horizontal
- **Button Padding:** 8px horizontal, 4px vertical (min 44px height for mobile)

### Icons
- **Source:** Heroicons (built-in to Tailwind) or Font Awesome
- **Usage:** 
  - 📊 = chart/analytics
  - 📞 = calls
  - 💳 = payments
  - 👥 = agents/users
  - 🎯 = targets/goals
  - 🔔 = notifications
  - ⚙️ = settings

### Interactions
- **Hover Effects:** Slight scale (1.02x), shadow increase, color shift
- **Click Feedback:** Button press animation (shrink slightly)
- **Loading State:** Skeleton loaders or spinner
- **Success Feedback:** Toast notification with ✅ icon
- **Error Feedback:** Toast notification with ❌ icon, error message

### Accessibility (WCAG 2.1 AA)
- **Color Contrast:** Minimum 4.5:1 for normal text, 3:1 for large text
- **Focus States:** Visible keyboard focus ring on all interactive elements
- **Alt Text:** Descriptive alt text for all images/icons
- **Keyboard Navigation:** All features accessible via keyboard (Tab, Enter, Arrow keys)
- **Screen Reader Support:** Proper ARIA labels and semantic HTML

---

**Document Version:** 2.0 | **Last Updated:** April 2, 2026 | **Status:** Complete & Ready for Development
