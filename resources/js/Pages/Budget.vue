<template>
  <AppLayout>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Budget Planner</h1>
          <p class="page-subtitle">Monitor your spending limits across categories.</p>
        </div>
        <button class="btn-set" @click="openModal()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Set Budget
        </button>
      </div>

      <div class="table-card">
        <table class="budget-table">
          <thead>
            <tr>
              <th>Category</th><th>Usage</th>
              <th class="text-right">Budget Limit</th>
              <th class="text-right">Used Amount</th>
              <th class="text-right">Remaining</th>
              <th class="text-center">Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in budgets" :key="item.id" class="budget-row" :style="`--delay: ${i * 0.07}s`">
              <td class="td-category">{{ item.category }}</td>
              <td class="td-usage">
                <div class="usage-wrap">
                  <div class="progress-track">
                    <div class="progress-bar" :style="`width: ${Math.min(item.pct, 100)}%; background: ${barColor(item.pct)}`"></div>
                  </div>
                  <span class="pct-label" :style="`color: ${barColor(item.pct)}`">{{ item.pct }}%</span>
                </div>
              </td>
              <td class="td-num">Rp {{ fmt(item.limit) }}</td>
              <td class="td-num">Rp {{ fmt(item.used) }}</td>
              <td class="td-num" :class="item.remaining < 0 ? 'neg' : 'pos'">{{ item.remaining < 0 ? '-' : '' }}Rp {{ fmt(Math.abs(item.remaining)) }}</td>
              <td class="td-status"><span :class="['status-badge', statusClass(item.pct)]">{{ statusLabel(item.pct) }}</span></td>
              <td class="td-actions">
                <button class="action-btn edit"   @click="openModal(item)"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                <button class="action-btn delete" @click="deleteBudget(item.id)"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
              </td>
            </tr>
            <tr v-if="budgets.length === 0"><td colspan="7" class="empty-state">No budgets set yet. Click "Set Budget" to get started.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2 class="modal-title">{{ editMode ? 'Edit Budget' : 'Set Budget' }}</h2>
          <button class="modal-close" @click="closeModal"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Category</label>
            <select v-model="form.category_id" :class="['form-input', formErrors.category_id ? 'input-error' : '']">
              <option value="">Select category</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <span v-if="formErrors.category_id" class="field-error">{{ formErrors.category_id }}</span>
          </div>
          <div class="form-group">
            <label>Period</label>
            <select v-model="form.period" class="form-input">
              <option value="monthly">Monthly</option>
              <option value="weekly">Weekly</option>
              <option value="yearly">Yearly</option>
            </select>
          </div>
          <div class="form-group">
            <label>Budget Limit (Rp)</label>
            <input v-model="form.limit_amount" type="number" :class="['form-input', formErrors.limit_amount ? 'input-error' : '']" placeholder="e.g. 1000" min="1" step="0.01"/>
            <span v-if="formErrors.limit_amount" class="field-error">{{ formErrors.limit_amount }}</span>
          </div>
          <div class="form-group">
            <label>Used Amount (Rp)</label>
            <input v-model="form.used_amount" type="number" class="form-input" placeholder="e.g. 700" min="0" step="0.01"/>
          </div>
          <!-- Preview -->
          <div class="preview-box" v-if="form.limit_amount > 0">
            <div class="preview-row">
              <span class="preview-label">Usage</span>
              <span class="preview-pct" :style="`color: ${barColor(previewPct)}`">{{ previewPct }}%</span>
            </div>
            <div class="progress-track"><div class="progress-bar" :style="`width: ${Math.min(previewPct,100)}%; background: ${barColor(previewPct)}`"></div></div>
            <div class="preview-row" style="margin-top:8px">
              <span class="preview-label">Status</span>
              <span :class="['status-badge', statusClass(previewPct)]">{{ statusLabel(previewPct) }}</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="closeModal">Cancel</button>
          <button class="btn-save" @click="saveBudget" :disabled="saving">
            {{ saving ? 'Saving...' : (editMode ? 'Save Changes' : 'Set Budget') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="deleteConfirm.show" class="modal-overlay" @click.self="cancelDelete">
      <div class="modal delete-modal">
        <div class="delete-icon-wrap">
          <div class="delete-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          </div>
        </div>
        <div class="delete-body">
          <h3 class="delete-title">{{ deleteConfirm.title }}</h3>
          <p class="delete-desc">{{ deleteConfirm.message }}</p>
        </div>
        <div class="delete-footer">
          <button class="btn-cancel" @click="cancelDelete">Batal</button>
          <button class="btn-delete" @click="confirmDelete" :disabled="deleteConfirm.loading">
            <svg v-if="deleteConfirm.loading" class="spin-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            {{ deleteConfirm.loading ? 'Menghapus...' : 'Ya, Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router }  from '@inertiajs/vue3'

export default {
  name: 'Budget',
  components: { AppLayout },
  props: {
    budgets:    { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
  },
  data() {
    return {
      showModal: false, editMode: false, saving: false,
      formErrors: {},
      deleteConfirm: { show: false, title: '', message: '', onConfirm: null, loading: false },
      form: { id: null, category_id: '', period: 'monthly', limit_amount: '', used_amount: '' },
    }
  },
  computed: {
    previewPct() {
      if (!this.form.limit_amount || this.form.limit_amount <= 0) return 0
      return Math.round(((this.form.used_amount || 0) / this.form.limit_amount) * 100)
    }
  },
  methods: {
    fmt(val) { return Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
    barColor(pct)    { return pct >= 100 ? '#ef4444' : pct >= 80 ? '#f59e0b' : '#10b981' },
    statusLabel(pct) { return pct >= 100 ? 'Exceeded' : pct >= 80 ? 'Warning' : 'Good' },
    statusClass(pct) { return pct >= 100 ? 'exceeded' : pct >= 80 ? 'warning' : 'good' },
    openModal(item = null) {
      if (item) {
        this.editMode = true
        this.form = { id: item.id, category_id: item.category_id, period: item.period, limit_amount: item.limit, used_amount: item.used }
      } else {
        this.editMode = false
        this.form = { id: null, category_id: '', period: 'monthly', limit_amount: '', used_amount: '' }
      }
      this.formErrors = {}
      this.showModal = true
    },
    closeModal() { this.showModal = false; this.formErrors = {} },
    saveBudget() {
      this.formErrors = {}
      if (!this.form.category_id) this.formErrors.category_id = 'Pilih kategori terlebih dahulu.'
      if (!this.form.limit_amount || Number(this.form.limit_amount) <= 0) this.formErrors.limit_amount = 'Budget limit harus lebih dari 0.'
      if (Object.keys(this.formErrors).length) return
      this.saving = true
      if (this.editMode) {
        router.put(`/budget/${this.form.id}`, this.form, {
          onSuccess: () => {
            this.closeModal(); this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Budget berhasil diupdate!', type: 'success' } }))
          },
          onError: (errors) => { this.formErrors = errors; this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Gagal menyimpan budget.', type: 'error' } }))
          }
        })
      } else {
        router.post('/budget', this.form, {
          onSuccess: () => {
            this.closeModal(); this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Budget berhasil ditambahkan!', type: 'success' } }))
          },
          onError: (errors) => { this.formErrors = errors; this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Gagal menyimpan budget.', type: 'error' } }))
          }
        })
      }
    },
    deleteBudget(id) {
      this.openDeleteConfirm(
        'Hapus Budget',
        'Budget ini akan dihapus permanen. Data used amount pada kategori ini tidak akan berubah.',
        () => router.delete(`/budget/${id}`, { onSuccess: () => { this.cancelDelete(); window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Budget berhasil dihapus.', type: 'success' } })) } })
      )
    },
    openDeleteConfirm(title, message, onConfirm) {
      this.deleteConfirm = { show: true, title, message, onConfirm, loading: false }
    },
    cancelDelete() {
      this.deleteConfirm = { show: false, title: '', message: '', onConfirm: null, loading: false }
    },
    confirmDelete() {
      if (!this.deleteConfirm.onConfirm) return
      this.deleteConfirm.loading = true
      this.deleteConfirm.onConfirm()
    },
  }
}
</script>

<style scoped>
.page-content { padding: 32px; display: flex; flex-direction: column; gap: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; animation: fadeUp 0.35s ease both; }
.page-title { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 800; color: var(--text-heading); letter-spacing: -0.01em; }
.page-subtitle { font-size: 14px; color: #64748b; margin-top: 4px; }
.btn-set { display: flex; align-items: center; gap: 8px; background: #10b981; color: #fff; border: none; border-radius: 10px; padding: 11px 20px; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.2s, transform 0.15s; }
.btn-set:hover { background: #059669; transform: translateY(-1px); }
.table-card { background: var(--card); transition: background 0.3s; border: 1px solid var(--border); border-radius: 16px; overflow: hidden; animation: fadeUp 0.35s ease 0.1s both; }
.budget-table { width: 100%; border-collapse: collapse; }
.budget-table thead tr { border-bottom: 1px solid var(--border); }
.budget-table th { padding: 14px 20px; font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; text-align: left; }
.text-right { text-align: right !important; } .text-center { text-align: center !important; }
.budget-row { border-bottom: 1px solid var(--border); animation: fadeUp 0.3s ease both; animation-delay: var(--delay); transition: background 0.15s; }
.budget-row:last-child { border-bottom: none; } .budget-row:hover { background: var(--border); }
.budget-table td { padding: 18px 20px; font-size: 14px; vertical-align: middle; }
.td-category { font-weight: 500; color: var(--text-body); }
.td-usage { min-width: 200px; }
.usage-wrap { display: flex; align-items: center; gap: 10px; }
.progress-track { flex: 1; height: 8px; background: var(--card2); border-radius: 99px; overflow: hidden; }
.progress-bar { height: 100%; border-radius: 99px; transition: width 0.6s ease; }
.pct-label { font-size: 13px; font-weight: 600; min-width: 38px; text-align: right; }
.td-num { text-align: right; font-weight: 500; color: var(--text-sub); font-variant-numeric: tabular-nums; }
.td-num.pos { color: #10b981; font-weight: 600; } .td-num.neg { color: #ef4444; font-weight: 600; }
.td-status { text-align: center; }
.status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.status-badge.good     { background: #10b98120; color: #10b981; border: 1px solid #10b98130; }
.status-badge.warning  { background: #f59e0b20; color: #f59e0b; border: 1px solid #f59e0b30; }
.status-badge.exceeded { background: #ef444420; color: #ef4444; border: 1px solid #ef444430; }
.td-actions { text-align: center; }
.action-btn { background: none; border: none; padding: 6px; border-radius: 7px; cursor: pointer; color: #475569; transition: all 0.15s; margin: 0 2px; }
.action-btn.edit:hover   { color: #3b82f6; background: #3b82f615; }
.action-btn.delete:hover { color: #ef4444; background: #ef444415; }
.empty-state { text-align: center; color: #475569; font-size: 14px; padding: 40px !important; }
.modal-overlay { position: fixed; inset: 0; background: #00000080; backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: var(--card); transition: background 0.3s; border: 1px solid var(--border2); border-radius: 20px; width: 100%; max-width: 420px; animation: slideUp 0.25s ease; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 24px 24px 0; }
.modal-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--text-heading); }
.modal-close { background: var(--card2); border: 1px solid var(--border2); border-radius: 8px; padding: 6px; color: #64748b; cursor: pointer; display: flex; align-items: center; }
.modal-close:hover { color: #ef4444; }
.modal-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 7px; }
.form-group label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.form-input { background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; padding: 11px 14px; font-size: 14px; color: var(--text); font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; width: 100%; }
.form-input:focus { border-color: #10b98150; } .form-input option { background: var(--card); }
.preview-box { background: var(--card2); border: 1px solid var(--border2); border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 10px; }
.preview-row { display: flex; justify-content: space-between; align-items: center; }
.preview-label { font-size: 12px; color: #64748b; font-weight: 500; }
.preview-pct { font-size: 13px; font-weight: 700; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 0 24px 24px; }
.btn-cancel { padding: 10px 20px; background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; color: var(--text-sub); font-size: 14px; font-weight: 500; font-family: 'DM Sans', sans-serif; cursor: pointer; }
.btn-save { padding: 10px 22px; background: #10b981; border: none; border-radius: 10px; color: #fff; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.2s, transform 0.15s; }
.btn-save:hover:not(:disabled) { background: #059669; transform: translateY(-1px); }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
.input-error { border-color: #ef4444 !important; }
.field-error { font-size: 12px; color: #ef4444; margin-top: 2px; display: block; }

.delete-modal { max-width: 380px !important; padding: 0; overflow: hidden; }
.delete-icon-wrap { display: flex; justify-content: center; padding: 32px 24px 0; }
.delete-icon { width: 64px; height: 64px; border-radius: 50%; background: #ef444415; border: 1px solid #ef444430; display: flex; align-items: center; justify-content: center; }
.delete-body { padding: 20px 28px 8px; text-align: center; }
.delete-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--text-heading); margin-bottom: 8px; }
.delete-desc { font-size: 14px; color: #64748b; line-height: 1.6; }
.delete-footer { display: flex; gap: 10px; padding: 24px 28px 28px; }
.delete-footer .btn-cancel { flex: 1; text-align: center; }
.btn-delete { flex: 1; display: flex; align-items: center; justify-content: center; gap: 7px; padding: 10px 20px; background: #ef4444; border: none; border-radius: 10px; color: #fff; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.2s, transform 0.15s; }
.btn-delete:hover:not(:disabled) { background: #dc2626; transform: translateY(-1px); }
.btn-delete:disabled { opacity: 0.6; cursor: not-allowed; }
@keyframes spin { to { transform: rotate(360deg); } }
.spin-icon { animation: spin 0.7s linear infinite; }

/* ── Responsive Budget ────────────────────────────── */
@media (max-width: 1024px) {
  .page-content { padding: 20px; }
}
@media (max-width: 768px) {
  .page-content { padding: 16px; gap: 16px; }
  .page-title   { font-size: 20px; }
  .page-header  { flex-wrap: wrap; gap: 10px; }

  /* Table → Card list */
  .table-card { border-radius: 12px; }
  .budget-table thead { display: none; }
  .budget-table, .budget-table tbody, .budget-row, .budget-table td { display: block; width: 100%; }
  .budget-row { padding: 16px; border-bottom: 1px solid var(--border); }
  .budget-row:last-child { border-bottom: none; }
  .budget-table td { padding: 0; border: none; }
  .td-category  { font-size: 15px; font-weight: 600; margin-bottom: 10px; }
  .td-usage     { margin-bottom: 8px; }
  .td-num       { display: inline-block; font-size: 13px; margin-right: 8px; text-align: left !important; }
  .td-num::before { content: attr(data-label); color: #64748b; font-size: 11px; font-weight: 500; margin-right: 4px; }
  .td-status    { margin-top: 8px; }
  .td-actions   { margin-top: 10px; display: flex; gap: 8px; justify-content: flex-end; }
  .text-right, .text-center { text-align: left !important; }
}

</style>