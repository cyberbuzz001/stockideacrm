<!-- Bulk Text Import Modal -->
<div id="bulkTextModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-black text-slate-900">📝 Bulk Text Import</h3>
            <button onclick="document.getElementById('bulkTextModal').classList.add('hidden')"
                class="text-slate-400 hover:text-slate-600 text-2xl font-bold">×</button>
        </div>

        <form action="{{ route('leads.bulk-text-import') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                    Data Format
                </label>
                <select name="format" required
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 font-medium">
                    <option value="mobile_only">Mobile Only (one per line)</option>
                    <option value="name_mobile">Name, Mobile (comma/tab separated)</option>
                    <option value="name_mobile_email">Name, Mobile, Email (comma/tab separated)</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                    Paste Lead Data (One per line)
                </label>
                <textarea name="bulk_text" rows="12" required
                    placeholder="Example for 'Name, Mobile' format:&#10;John Doe, 9876543210&#10;Jane Smith, 9123456789&#10;&#10;Or for 'Mobile Only':&#10;9876543210&#10;9123456789"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 font-mono text-sm"></textarea>
                <p class="mt-2 text-xs text-slate-500">
                    💡 Tip: You can use comma, tab, or pipe (|) as separators. Duplicates will be automatically skipped.
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('bulkTextModal').classList.add('hidden')"
                    class="px-6 py-3 bg-slate-100 text-slate-700 rounded-xl font-bold hover:bg-slate-200">
                    Cancel
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-purple-600 text-white rounded-xl font-bold shadow-lg shadow-purple-100 hover:bg-purple-700">
                    Import Leads
                </button>
            </div>
        </form>
    </div>
</div>