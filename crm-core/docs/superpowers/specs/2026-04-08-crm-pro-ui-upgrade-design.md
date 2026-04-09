# Design Specification: CRM Pro UI & Productivity Upgrade

**Date**: 2026-04-08
**Topic**: High-performance "Soft UI" redesign and productivity automation.

## 1. Vision & Aesthetic
The CRM will transition from a standard enterprise look to a **Soft UI Evolution** aesthetic. This focuses on reducing cognitive load for high-velocity agents while maintaining a premium, "SaaS-native" feel.

- **Design Language**: Glassmorphism (back-drop blurs), soft elevation (subtle shadows), and rounded corners (16px+).
- **Typography**: Inter (Sans-serif) with high-contrast hierarchical weighting.
- **Color Palette**: 
    - **Backdrop**: Slate-50 / Slate-900 (Dark Support)
    - **Primary**: Electric Blue (`#2563EB`)
    - **Success/Deals**: Emerald Green (`#059669`)

## 2. Core Features

### A. Bento Roadmap (Dashboard)
Transform the "Command Center" into a **Bento Grid** layout.
- **Priority Card**: A high-visibility card showing the very next call/activity due.
- **Daily Progress**: A circular progress ring (Soft UI style) showing MTD revenue vs Target.
- **Live Feed**: Small, scrolling ticker of "Recent Wins" (Leads closed).

### B. Semi-Automated Communication Logging
Enhance the Lead Profile with action-oriented buttons.
- **Action**: "Quick Message" (WhatsApp/Telegram).
- **Interaction**: Opens a new tab with the direct chat URL and a pre-formatted message template from system settings.
- **Automation**: Upon clicking, an `activity_type = 'whatsapp'` or `'telegram'` entry is automatically created in `lead_activities` to track agent reach-out without manual entry.

### C. Real-time Notifications (Reverb)
Leverage existing Reverb infrastructure for zero-latency alerts.
- **In-App Toasts**: Soft UI glassmorphic toasts for:
    - New Lead assigned to you.
    - Advisory Call broadcast from Admin.
    - Target achieved alerts.

## 3. Technical Architecture
- **CSS Framework**: Tailwind CSS (extending with custom glassmorphism utilities).
- **Icons**: Lucide Icons (replacing any generic emojis or FontAwesome).
- **State Management**: Livewire 3 / Alpine.js for smooth, non-reloading component updates.

## 4. Proposed Timeline
- **Phase 1**: UI Shell & Bento Dashboard upgrade.
- **Phase 2**: Communication Buttons & Activity Logging logic.
- **Phase 3**: Real-time Notification listener components.

---

## User Review Required

> [!IMPORTANT]
> The "Quick Message" feature will rely on the agent having WhatsApp/Telegram Desktop installed or using the Web version. It will not "read" the chat history, strictly log that a "Send Message" attempt was initiated.
