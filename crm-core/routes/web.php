<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LeadController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\AiAnalysisController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeadDocumentController;
use App\Http\Controllers\DataAccessReportController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\KycRpmController;
use App\Http\Controllers\MessageTemplateController;

Route::get('/', function () {
    return view('welcome', [
        'company_name' => \App\Models\SystemSetting::get('company_name', 'StockIdea'),
        'company_logo' => \App\Models\SystemSetting::get('company_logo')
    ]);
});

// Temporary Route to install Database on Shared Hosting (Hostinger)
if (config('app.env') !== 'production') {
    Route::get('/setup-db-force', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            return 'Database Migrated Successfully! <a href="/">Go Home</a>';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });
}

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// PWA Offline Fallback
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Live Leaderboard API (JSON, polled every 30s from dashboard)
Route::get('/leaderboard/live', [DashboardController::class, 'leaderboardLive'])
    ->middleware(['auth'])
    ->name('leaderboard.live');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Global Search API
    Route::get('/search', [\App\Http\Controllers\GlobalSearchController::class, 'index'])
        ->middleware(['throttle:60,1'])
        ->name('search');

    // Re-allocation Tool (Admin/Manager only)
    Route::get('/leads/reallocate', [\App\Http\Controllers\LeadReallocationController::class, 'index'])->name('leads.reallocate');
    Route::post('/leads/reallocate', [\App\Http\Controllers\LeadReallocationController::class, 'transfer'])->name('leads.transfer');

    // AI CRM Routes
    Route::middleware([\App\Http\Middleware\CheckMandatoryTraining::class])->group(function () {
        Route::post('leads/fetch', [LeadController::class, 'fetchLeads'])->name('leads.fetch');
        Route::get('leads/export', [LeadController::class, 'export'])->name('leads.export');
        Route::post('leads/import', [LeadController::class, 'import'])->name('leads.import');
        Route::post('leads/bulk-text-import', [LeadController::class, 'bulkTextImport'])->name('leads.bulk-text-import');
        Route::post('leads/bulk-delete', [LeadController::class, 'bulkDelete'])->name('leads.bulk-delete');
        Route::get('leads/assign', function () {
            return redirect()->route('leads.index')->with('error', 'Please use the Assign button from the leads list or lead detail page.');
        })->name('leads.assign.get');
        Route::post('leads/assign', [LeadController::class, 'assign'])->name('leads.assign');
        Route::post('leads/bulk-status', [LeadController::class, 'bulkStatus'])->name('leads.bulk-status');
        Route::post('leads/auto-distribute', [LeadController::class, 'autoDistribute'])->name('leads.auto-distribute');
        
        Route::resource('leads', LeadController::class);

        // Chat Window Endpoints
        Route::get('leads/{lead}/chat-history', [LeadController::class, 'getChatHistory'])->name('leads.chat-history');
        Route::post('leads/{lead}/chat-send', [LeadController::class, 'sendChatMessage'])->name('leads.chat-send');
        Route::post('leads/{lead}/chat-send-template', [LeadController::class, 'sendChatTemplate'])->name('leads.chat-send-template');
    });

    Route::post('leads/{lead}/start-call', [LeadController::class, 'startCall'])->name('leads.start-call');
    Route::post('leads/{lead}/activity', [LeadController::class, 'storeActivity'])->name('leads.activity');
    Route::post('leads/{lead}/notes', [LeadController::class, 'saveNotes'])->name('leads.notes');
    Route::post('leads/{lead}/messages', [LeadController::class, 'storeMessage'])->name('leads.messages');
    Route::post('leads/{lead}/send-whatsapp', [LeadController::class, 'sendWhatsApp'])->name('leads.send-whatsapp');
    Route::post('leads/{lead}/ai-draft', [LeadController::class, 'aiDraftMessage'])->name('leads.ai-draft');
    Route::post('leads/{lead}/update-rich-details', [LeadController::class, 'updateRichDetails'])->name('leads.update-rich-details');
    Route::post('leads/{lead}/quick-log', [LeadController::class, 'quickLog'])->name('leads.quick-log');
    Route::post('leads/{lead}/log-comm', [LeadController::class, 'logComm'])->name('leads.log-comm');
    Route::post('leads/{lead}/escalate', [LeadController::class, 'escalate'])->name('leads.escalate');
    Route::post('leads/{lead}/compliance/step', [LeadController::class, 'completeComplianceStep'])->name('leads.compliance.step');
    Route::post('leads/{lead}/compliance/expiry', [LeadController::class, 'updateComplianceExpiry'])->name('leads.compliance.expiry');
    Route::post('leads/{lead}/consent/grant', [LeadController::class, 'grantConsent'])->name('leads.consent.grant');
    Route::post('leads/{lead}/consent/revoke', [LeadController::class, 'revokeConsent'])->name('leads.consent.revoke');
    Route::post('leads/{lead}/documents', [LeadDocumentController::class, 'upload'])->name('leads.documents.upload');
    Route::get('documents/{token}', [LeadDocumentController::class, 'download'])->name('documents.download');

    // Lead Status Management
    Route::post('leads/{lead}/update-status', [\App\Http\Controllers\LeadStatusController::class, 'updateStatus'])->name('leads.update-status');
    Route::get('status-definitions', [\App\Http\Controllers\LeadStatusController::class, 'getAllDefinitions'])->name('status.definitions');
    Route::get('status-definitions/{status}', [\App\Http\Controllers\LeadStatusController::class, 'getStatusDefinition'])->name('status.definition');

    Route::get('notifications/counts', [\App\Http\Controllers\NotificationController::class, 'getCounts'])->name('notifications.counts');
    Route::get('notifications/check', [DashboardController::class, 'checkNotifications'])->name('notifications.check');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::resource('employees', EmployeeController::class);
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::get('payments/{payment}/invoice', [PaymentController::class, 'invoice'])->name('payments.invoice');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Paid Clients Module (Sections 11-12)
    Route::get('clients/retention', [ClientController::class, 'retention'])->name('clients.retention');
    Route::resource('clients', ClientController::class);
    Route::post('clients/{client}/renewal', [ClientController::class, 'updateRenewal'])->name('clients.renewal');
    Route::post('clients/{client}/revert', [ClientController::class, 'revert'])->name('clients.revert');
    Route::post('clients/{client}/payments', [ClientController::class, 'storePayment'])->name('clients.payments.store');

    Route::get('sales-orders', [PaymentController::class, 'salesOrders'])->name('payments.sales-orders');

    // Monthly Targets
    Route::get('targets', [TargetController::class, 'index'])->name('targets.index');
    Route::post('targets', [TargetController::class, 'store'])->name('targets.store');

    // Performance Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // Attendance Monitoring
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/report/monthly', [AttendanceController::class, 'monthlyReport'])->name('attendance.report.monthly');
    Route::get('attendance/report/monthly/export', [AttendanceController::class, 'exportMonthly'])->name('attendance.report.monthly.export');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/heartbeat', [AttendanceController::class, 'heartbeat'])->name('attendance.heartbeat');
    
    // Performance Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // AI Analysis (Admin/Manager Only)
    Route::middleware(['role:Admin,Manager'])->group(function () {
        Route::get('/ai/analysis', [AiAnalysisController::class, 'index'])->name('ai.index');
        Route::post('/ai/analyze', [AiAnalysisController::class, 'analyze'])->name('ai.analyze');
        
        // AI Smart Response Sidebar Endpoints
        Route::get('/ai/summarize/{lead}', [AiAnalysisController::class, 'summarize'])->name('ai.summarize');
        Route::post('/ai/get-advice/{lead}', [AiAnalysisController::class, 'getAdvice'])->name('ai.get-advice');
        Route::post('/analytics/audit', [AnalyticsController::class, 'runAudit'])->name('analytics.audit');
        Route::post('/leads/{lead}/whatsapp/send', [LeadController::class, 'dispatchComplianceWhatsApp'])->name('leads.whatsapp.send');

        // Activity Logs
        Route::get('/activities', [App\Http\Controllers\SystemActivityController::class, 'index'])->name('activities.index');
        Route::get('/system-logs', [App\Http\Controllers\LogViewerController::class, 'index'])->name('admin.logs');
        Route::post('/system-logs/clear', [App\Http\Controllers\LogViewerController::class, 'clear'])->name('admin.logs.clear');
        Route::get('/admin/data-access', [DataAccessReportController::class, 'index'])->name('admin.data-access');
        Route::get('/admin/consents', [ConsentController::class, 'index'])->name('admin.consents');
    });

    // Admin Control Center (Admin Only)
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/admin/control-center', [\App\Http\Controllers\AdminControlController::class, 'index'])->name('admin.control-center');
        Route::post('/admin/control-center/update', [\App\Http\Controllers\AdminControlController::class, 'update'])->name('admin.settings.update');
        Route::post('/admin/control-center/branding', [\App\Http\Controllers\AdminControlController::class, 'updateBranding'])->name('admin.settings.branding');
        Route::post('/admin/control-center/attendance-override', [\App\Http\Controllers\AdminControlController::class, 'overrideAttendance'])->name('admin.attendance.override');
        Route::post('/admin/control-center/ticker', [\App\Http\Controllers\AdminControlController::class, 'updateTicker'])->name('admin.ticker.update');
        Route::get('/admin/whatsapp/qrcode', [\App\Http\Controllers\AdminControlController::class, 'getWhatsAppQRCode'])->name('admin.whatsapp.qrcode');

        // Learning & Training Module (Admin)
        Route::get('/admin/training', [\App\Http\Controllers\AdminTrainingController::class, 'index'])->name('admin.training.index');
        Route::post('/admin/training', [\App\Http\Controllers\AdminTrainingController::class, 'upload'])->name('admin.training.upload');
        Route::delete('/admin/training/{module}', [\App\Http\Controllers\AdminTrainingController::class, 'destroy'])->name('admin.training.destroy');

        // Message Templates
        Route::resource('message-templates', \App\Http\Controllers\MessageTemplateController::class);
        Route::post('message-templates/sync-meta', [\App\Http\Controllers\MessageTemplateController::class, 'syncMetaTemplates'])->name('message-templates.sync-meta');

        // Role & Permissions Matrix
        Route::get('/admin/roles/matrix', [\App\Http\Controllers\RolePermissionController::class, 'index'])->name('admin.roles.matrix');
        Route::post('/admin/roles/matrix', [\App\Http\Controllers\RolePermissionController::class, 'update'])->name('admin.roles.update');

        // Lead Flushing (Cleanup)
        Route::post('leads/flush', [LeadController::class, 'flush'])->name('leads.flush');
    });

    Route::get('message-templates-list', [MessageTemplateController::class, 'list'])->name('message-templates.list');

    // KYC / RPM Compliance ??? All Employees
    Route::get('/kyc-rpm', [KycRpmController::class, 'index'])->name('kyc-rpm.index');
    Route::post('/kyc-rpm/{lead}/update-data', [KycRpmController::class, 'updateData'])->name('kyc-rpm.update-data');
    Route::get('/leads/{lead}/kyc-rpm', [KycRpmController::class, 'show'])->name('kyc-rpm.show');

    // Advisory Calls (Market Call Distribution)
    Route::resource('advisory-calls', \App\Http\Controllers\AdvisoryCallController::class);
    Route::post('advisory-calls/{advisory_call}/outcome', [\App\Http\Controllers\AdvisoryCallController::class, 'updateOutcome'])->name('advisory-calls.outcome');
    Route::post('advisory-calls/broadcast', [\App\Http\Controllers\AdvisoryCallController::class, 'broadcast'])->name('advisory-calls.broadcast');



    // Internal Mailing System
    Route::resource('internal-mails', \App\Http\Controllers\InternalMailController::class);

    // Learning & Training Module (Agent)
    Route::get('/agent/learning', [\App\Http\Controllers\AgentLearningController::class, 'index'])->name('agent.learning.index');
    Route::get('/agent/learning/{module}', [\App\Http\Controllers\AgentLearningController::class, 'show'])->name('agent.learning.show');
    Route::post('/agent/learning/{module}/complete', [\App\Http\Controllers\AgentLearningController::class, 'complete'])->name('agent.learning.complete');
});

require __DIR__ . '/auth.php';

