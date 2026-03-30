<template>
  <AppLayout>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Transactions</h1>
          <p class="page-subtitle">Manage your income and expenses.</p>
        </div>
        <button class="btn-add" @click="openModal()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add Transaction
        </button>
      </div>

      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="filter-search">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input v-model="localSearch" @input="applyFilters" type="text" placeholder="Search transactions..." />
        </div>
        <div class="filter-right">
          <select v-model="localMonth" @change="applyFilters" class="filter-select">
            <option value="">All Months</option>
            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
          <select v-model="localType" @change="applyFilters" class="filter-select">
            <option value="">All Types</option>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
            <option value="transfer">Transfer</option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="table-card">
        <table class="tx-table">
          <thead>
            <tr>
              <th>Date</th><th>Category</th><th>Description</th>
              <th class="text-right">Amount</th><th class="text-center">Type</th><th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(tx, i) in transactions" :key="tx.id" class="tx-row" :style="`--delay: ${i * 0.05}s`">
              <td class="td-date">{{ tx.date }}</td>
              <td class="td-category">{{ tx.category }}</td>
              <td class="td-desc">{{ tx.description }}</td>
              <td :class="['td-amount', tx.type]">{{ tx.type === 'income' ? '+' : tx.type === 'transfer' ? '⇄' : '-' }}Rp {{ tx.amount }}</td>
              <td class="td-type"><span :class="['type-badge', tx.type]">{{ tx.type === 'income' ? 'Income' : tx.type === 'expense' ? 'Expense' : 'Transfer' }}</span></td>
              <td class="td-actions">
                <button class="action-btn edit"   @click="openModal(tx)"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                <button class="action-btn delete" @click="deleteTransaction(tx.id)"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
              </td>
            </tr>
            <tr v-if="transactions.length === 0">
              <td colspan="6" class="empty-state">No transactions found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2 class="modal-title">{{ editMode ? 'Edit Transaction' : 'Add Transaction' }}</h2>
          <button class="modal-close" @click="closeModal"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Date</label>
            <input v-model="form.date" type="date" :class="['form-input', formErrors.date ? 'input-error' : '']"/>
            <span v-if="formErrors.date" class="field-error">{{ formErrors.date }}</span>
          </div>
          <div class="form-group">
            <label>Category</label>
            <select v-model="form.category_id" :class="['form-input', formErrors.category_id ? 'input-error' : '']">
              <option value="">Select category</option>
              <option v-for="c in filteredCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <span v-if="formErrors.category_id" class="field-error">{{ formErrors.category_id }}</span>
          </div>
          <div class="form-group">
            <label>Description</label>
            <input v-model="form.description" type="text" :class="['form-input', formErrors.description ? 'input-error' : '']" placeholder="e.g. October Salary"/>
            <span v-if="formErrors.description" class="field-error">{{ formErrors.description }}</span>
          </div>
          <div class="form-group">
            <label>Amount (Rp)</label>
            <input v-model="form.amount" type="number" :class="['form-input', formErrors.amount ? 'input-error' : '']" placeholder="0" min="0.01" step="0.01"/>
            <span v-if="formErrors.amount" class="field-error">{{ formErrors.amount }}</span>
          </div>
          <div class="form-group">
            <label>Type</label>
            <div class="type-toggle">
              <button :class="['toggle-btn', form.type === 'income'  ? 'active-income'  : '']" @click="form.type = 'income'">Income</button>
              <button :class="['toggle-btn', form.type === 'expense' ? 'active-expense' : '']" @click="form.type = 'expense'">Expense</button>
            </div>
          </div>
          <div class="form-group">
            <label>Notes <span class="opt">(optional)</span></label>
            <input v-model="form.notes" type="text" class="form-input" placeholder="Any additional notes..."/>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="closeModal">Cancel</button>
          <button class="btn-save" @click="saveTransaction" :disabled="saving">
            {{ saving ? 'Saving...' : (editMode ? 'Save Changes' : 'Add Transaction') }}
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
import { router } from '@inertiajs/vue3'

export default {
  name: 'Transactions',
  components: { AppLayout },
  props: {
    transactions: { type: Array,  default: () => [] },
    categories:   { type: Array,  default: () => [] },
    filters:         { type: Object, default: () => ({}) },
    availableMonths: { type: Array,  default: () => [] },
  },
  data() {
    return {
      showModal: false, editMode: false, saving: false,
      localSearch: this.filters?.search || '',
      localMonth:  this.filters?.month  || '',
      localType:   this.filters?.type   || '',
      form: { id: null, date: '', category_id: '', description: '', amount: '', type: 'expense', notes: '' },
      formErrors: {},
      deleteConfirm: { show: false, title: '', message: '', onConfirm: null, loading: false },
      filterTimeout: null,
    }
  },
  computed: {
    filteredCategories() {
      return this.categories.filter(c => c.type === this.form.type)
    },
    // Pakai availableMonths dari backend (query terpisah, tidak terpengaruh filter)
    months() {
      return this.availableMonths
    }
  },
  methods: {
    applyFilters() {
      clearTimeout(this.filterTimeout)
      this.filterTimeout = setTimeout(() => {
        router.get('/transactions', {
          search: this.localSearch,
          month:  this.localMonth,
          type:   this.localType,
        }, { preserveState: true, replace: true })
      }, 350)
    },
    openModal(tx = null) {
      if (tx) {
        this.editMode = true
        this.form = { id: tx.id, date: tx.date, category_id: tx.category_id, description: tx.description, amount: tx.amount.replace(/,/g,''), type: tx.type, notes: tx.notes || '' }
      } else {
        this.editMode = false
        this.form = { id: null, date: new Date().toISOString().slice(0,10), category_id: '', description: '', amount: '', type: 'expense', notes: '' }
      }
      this.formErrors = {}
      this.showModal = true
    },
    closeModal() { this.showModal = false; this.formErrors = {} },
    saveTransaction() {
      // Validasi realtime
      this.formErrors = {}
      if (!this.form.date)        this.formErrors.date = 'Tanggal wajib diisi.'
      if (!this.form.category_id) this.formErrors.category_id = 'Pilih kategori terlebih dahulu.'
      if (!this.form.description) this.formErrors.description = 'Deskripsi wajib diisi.'
      if (!this.form.amount || Number(this.form.amount) <= 0) this.formErrors.amount = 'Jumlah harus lebih dari 0.'
      if (Object.keys(this.formErrors).length) return

      this.saving = true
      if (this.editMode) {
        router.put(`/transactions/${this.form.id}`, this.form, {
          onSuccess: () => {
            this.closeModal(); this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Transaksi berhasil diupdate!', type: 'success' } }))
          },
          onError: (errors) => { this.formErrors = errors; this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Gagal menyimpan, cek kembali form.', type: 'error' } }))
          }
        })
      } else {
        router.post('/transactions', this.form, {
          onSuccess: () => {
            this.closeModal(); this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Transaksi berhasil ditambahkan!', type: 'success' } }))
          },
          onError: (errors) => { this.formErrors = errors; this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Gagal menyimpan, cek kembali form.', type: 'error' } }))
          }
        })
      }
    },
    deleteTransaction(id) {
      this.openDeleteConfirm(
        'Hapus Transaksi',
        'Transaksi ini akan dihapus permanen. Saldo dan budget terkait akan ikut diperbarui.',
        () => router.delete(`/transactions/${id}`, { onSuccess: () => { this.cancelDelete(); window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Transaksi berhasil dihapus.', type: 'success' } })) } })
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
.btn-add { display: flex; align-items: center; gap: 8px; background: #10b981; color: #fff; border: none; border-radius: 10px; padding: 11px 20px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background 0.2s, transform 0.15s; }
.btn-add:hover { background: #059669; transform: translateY(-1px); }
.filter-bar { display: flex; align-items: center; justify-content: space-between; background: var(--card); transition: background 0.3s, border-color 0.3s; border: 1px solid var(--border); border-radius: 14px; padding: 14px 18px; gap: 16px; animation: fadeUp 0.35s ease 0.06s both; }
.filter-search { display: flex; align-items: center; gap: 10px; color: #475569; flex: 1; }
.filter-search input { background: none; border: none; outline: none; font-size: 14px; color: var(--text-sub); font-family: 'DM Sans', sans-serif; width: 100%; }
.filter-search input::placeholder { color: #475569; }
.filter-right { display: flex; align-items: center; gap: 10px; }
.filter-select { background: var(--card2); border: 1px solid var(--border2); border-radius: 8px; padding: 8px 28px 8px 12px; font-size: 13px; color: var(--text-sub); font-family: 'DM Sans', sans-serif; cursor: pointer; outline: none; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; background-color: var(--card2); }
.table-card { background: var(--card); transition: background 0.3s; border: 1px solid var(--border); border-radius: 16px; overflow: hidden; animation: fadeUp 0.35s ease 0.12s both; }
.tx-table { width: 100%; border-collapse: collapse; }
.tx-table thead tr { border-bottom: 1px solid var(--border); }
.tx-table th { padding: 14px 20px; font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; text-align: left; }
.text-right { text-align: right !important; } .text-center { text-align: center !important; }
.tx-row { border-bottom: 1px solid var(--border); animation: fadeUp 0.3s ease both; animation-delay: var(--delay); transition: background 0.15s; }
.tx-row:last-child { border-bottom: none; } .tx-row:hover { background: var(--border); }
.tx-table td { padding: 16px 20px; font-size: 14px; }
.td-date { color: #64748b; } .td-category { color: var(--text-heading); font-weight: 600; } .td-desc { color: var(--text-sub); }
.td-amount { text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; }
.td-amount.income { color: #10b981; } .td-amount.expense { color: #ef4444; } .td-amount.transfer { color: #3b82f6; }
.td-type { text-align: center; }
.type-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.type-badge.income   { background: #10b98120; color: #10b981; border: 1px solid #10b98130; }
.type-badge.expense  { background: #ef444420; color: #ef4444; border: 1px solid #ef444430; }
.type-badge.transfer { background: #3b82f620; color: #3b82f6; border: 1px solid #3b82f630; }
.td-actions { text-align: center; }
.action-btn { background: none; border: none; padding: 6px; border-radius: 7px; cursor: pointer; color: #475569; transition: all 0.15s; margin: 0 2px; }
.action-btn.edit:hover   { color: #3b82f6; background: #3b82f615; }
.action-btn.delete:hover { color: #ef4444; background: #ef444415; }
.empty-state { text-align: center; color: #475569; font-size: 14px; padding: 40px !important; }
.modal-overlay { position: fixed; inset: 0; background: #00000080; backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 100; animation: fadeIn 0.2s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.modal { background: var(--card); transition: background 0.3s; border: 1px solid var(--border2); border-radius: 20px; width: 100%; max-width: 440px; animation: slideUp 0.25s ease; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 24px 24px 0; }
.modal-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--text-heading); }
.modal-close { background: var(--card2); border: 1px solid var(--border2); border-radius: 8px; padding: 6px; color: #64748b; cursor: pointer; display: flex; align-items: center; }
.modal-close:hover { color: #ef4444; }
.modal-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 7px; }
.form-group label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.opt { font-size: 11px; color: #475569; text-transform: none; font-weight: 400; }
.form-input { background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; padding: 11px 14px; font-size: 14px; color: var(--text); font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; width: 100%; }
.form-input:focus { border-color: #10b98150; } .form-input option { background: var(--card); }
.type-toggle { display: flex; gap: 8px; }
.toggle-btn { flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--border2); background: var(--card2); color: #64748b; font-size: 14px; font-weight: 500; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: all 0.2s; }
.toggle-btn.active-income  { background: #10b98120; color: #10b981; border-color: #10b98140; }
.toggle-btn.active-expense { background: #ef444420; color: #ef4444; border-color: #ef444440; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 0 24px 24px; }
.btn-cancel { padding: 10px 20px; background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; color: var(--text-sub); font-size: 14px; font-weight: 500; font-family: 'DM Sans', sans-serif; cursor: pointer; }
.btn-cancel:hover { border-color: #ffffff20; }
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

/* ── Responsive Transactions ─────────────────────── */
@media (max-width: 1024px) {
  .page-content { padding: 20px; }
  .filter-bar   { flex-wrap: wrap; gap: 10px; }
  .filter-right { flex-wrap: wrap; }
}
@media (max-width: 768px) {
  .page-content  { padding: 16px; gap: 16px; }
  .page-title    { font-size: 20px; }
  .page-header   { flex-wrap: wrap; gap: 10px; }
  .filter-bar    { padding: 12px; flex-direction: column; align-items: stretch; }
  .filter-right  { flex-direction: row; flex-wrap: wrap; }
  .filter-select { flex: 1; min-width: 120px; }

  /* Table → Card list on mobile */
  .table-card { border-radius: 12px; }
  .tx-table thead { display: none; }
  .tx-table, .tx-table tbody, .tx-row, .tx-table td { display: block; width: 100%; }
  .tx-row { padding: 14px 16px; border-bottom: 1px solid var(--border); position: relative; }
  .tx-row:last-child { border-bottom: none; }
  .tx-table td { padding: 0; border: none; }
  .td-date     { font-size: 11px; color: #64748b; margin-bottom: 4px; }
  .td-category { font-size: 15px; font-weight: 600; color: var(--text-heading); }
  .td-desc     { font-size: 13px; color: var(--text-sub); margin-top: 2px; }
  .td-amount   { position: absolute; top: 14px; right: 48px; font-size: 15px; font-weight: 700; text-align: right; }
  .td-type     { position: absolute; top: 40px; right: 48px; }
  .type-badge  { font-size: 10px; padding: 2px 8px; }
  .td-actions  { position: absolute; top: 14px; right: 8px; display: flex; flex-direction: column; gap: 4px; }
  .text-right, .text-center { text-align: left !important; }
}

</style>