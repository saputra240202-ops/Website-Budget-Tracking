<template>
  <AppLayout>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Categories</h1>
          <p class="page-subtitle">Manage transaction categories for better tracking.</p>
        </div>
      </div>

      <div class="categories-grid">
        <div class="category-card" v-for="section in sections" :key="section.type">
          <div class="card-header">
            <h2 class="card-title">{{ section.label }}</h2>
            <button class="btn-add-cat" @click="openModal(section.type)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Add
            </button>
          </div>
          <div class="category-list">
            <div v-for="(cat, i) in section.items" :key="cat.id" class="category-item" :style="`--delay: ${i * 0.07}s`">
              <div class="cat-icon" :style="`background: ${cat.color}`">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              </div>
              <span class="cat-name">{{ cat.name }}</span>
              <div class="cat-actions">
                <button class="action-btn edit"   @click="openModal(section.type, cat)"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                <button class="action-btn delete" @click="deleteCategory(cat.id)"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
              </div>
            </div>
            <div v-if="section.items.length === 0" class="empty-cat">No {{ section.type }} categories yet.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2 class="modal-title">{{ editMode ? 'Edit Category' : `Add ${modalType === 'income' ? 'Income' : 'Expense'} Category` }}</h2>
          <button class="modal-close" @click="closeModal"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Category Name</label>
            <input v-model="form.name" type="text" :class="['form-input', formErrors.name ? 'input-error' : '']" placeholder="e.g. Freelance" @input="formErrors.name = ''"/>
            <span v-if="formErrors.name" class="field-error">{{ formErrors.name }}</span>
          </div>
          <div class="form-group">
            <label>Icon Color</label>
            <div class="color-picker">
              <div v-for="color in colorOptions" :key="color" :class="['color-dot', { selected: form.color === color }]" :style="`background: ${color}`" @click="form.color = color"></div>
            </div>
          </div>
          <div class="form-group">
            <label>Preview</label>
            <div class="cat-preview">
              <div class="cat-icon" :style="`background: ${form.color}`">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              </div>
              <span class="cat-name">{{ form.name || 'Category Name' }}</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="closeModal">Cancel</button>
          <button class="btn-save" @click="saveCategory" :disabled="saving">
            {{ saving ? 'Saving...' : (editMode ? 'Save Changes' : 'Add Category') }}
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
  name: 'Categories',
  components: { AppLayout },
  props: {
    incomeCategories:  { type: Array, default: () => [] },
    expenseCategories: { type: Array, default: () => [] },
  },
  data() {
    return {
      showModal: false, editMode: false, modalType: 'income', saving: false,
      formErrors: {},
      deleteConfirm: { show: false, title: '', message: '', onConfirm: null, loading: false },
      form: { id: null, name: '', color: '#10b981' },
      colorOptions: ['#10b981','#3b82f6','#8b5cf6','#ec4899','#f59e0b','#ef4444','#06b6d4','#84cc16','#f97316','#6366f1'],
    }
  },
  computed: {
    sections() {
      return [
        { type: 'income',  label: 'Income Categories',  items: this.incomeCategories  },
        { type: 'expense', label: 'Expense Categories', items: this.expenseCategories },
      ]
    }
  },
  methods: {
    openModal(type, cat = null) {
      this.modalType = type
      if (cat) { this.editMode = true;  this.form = { id: cat.id, name: cat.name, color: cat.color } }
      else      { this.editMode = false; this.form = { id: null,   name: '',       color: '#10b981' } }
      this.formErrors = {}
      this.showModal = true
    },
    closeModal() { this.showModal = false; this.formErrors = {} },
    saveCategory() {
      this.formErrors = {}
      if (!this.form.name.trim()) { this.formErrors.name = 'Nama kategori wajib diisi.'; return }
      if (this.form.name.trim().length < 2) { this.formErrors.name = 'Nama minimal 2 karakter.'; return }
      this.saving = true
      if (this.editMode) {
        router.put(`/categories/${this.form.id}`, { name: this.form.name, color: this.form.color }, {
          onSuccess: () => {
            this.closeModal(); this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Kategori berhasil diupdate!', type: 'success' } }))
          },
          onError: (errors) => { this.formErrors = errors; this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Gagal menyimpan kategori.', type: 'error' } }))
          }
        })
      } else {
        router.post('/categories', { name: this.form.name, type: this.modalType, color: this.form.color }, {
          onSuccess: () => {
            this.closeModal(); this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Kategori berhasil ditambahkan!', type: 'success' } }))
          },
          onError: (errors) => { this.formErrors = errors; this.saving = false
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Gagal menyimpan kategori.', type: 'error' } }))
          }
        })
      }
    },
    deleteCategory(id) {
      this.openDeleteConfirm(
        'Hapus Kategori',
        'Kategori ini akan dihapus. Transaksi yang terkait tidak akan ikut terhapus.',
        () => router.delete(`/categories/${id}`, { onSuccess: () => { this.cancelDelete(); window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Kategori berhasil dihapus.', type: 'success' } })) } })
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
.page-header { animation: fadeUp 0.35s ease both; }
.page-title { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 800; color: var(--text-heading); letter-spacing: -0.01em; }
.page-subtitle { font-size: 14px; color: #64748b; margin-top: 4px; }
.categories-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; animation: fadeUp 0.35s ease 0.08s both; }
.category-card { background: var(--card); transition: background 0.3s, border-color 0.3s; border: 1px solid var(--border); border-radius: 18px; padding: 24px; }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.card-title { font-size: 16px; font-weight: 600; color: var(--text-heading); }
.btn-add-cat { display: flex; align-items: center; gap: 6px; background: none; border: none; color: #10b981; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; padding: 6px 10px; border-radius: 8px; transition: background 0.15s; }
.btn-add-cat:hover { background: #10b98115; }
.category-list { display: flex; flex-direction: column; gap: 4px; }
.category-item { display: flex; align-items: center; gap: 14px; padding: 12px 10px; border-radius: 12px; transition: background 0.15s; animation: fadeUp 0.3s ease both; animation-delay: var(--delay); }
.category-item:hover { background: var(--border); }
.cat-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.cat-name { flex: 1; font-size: 15px; font-weight: 500; color: var(--text-body); }
.cat-actions { display: flex; gap: 4px; opacity: 0; transition: opacity 0.15s; }
.category-item:hover .cat-actions { opacity: 1; }
.action-btn { background: none; border: none; padding: 6px; border-radius: 7px; cursor: pointer; color: #475569; transition: all 0.15s; }
.action-btn.edit:hover   { color: #3b82f6; background: #3b82f615; }
.action-btn.delete:hover { color: #ef4444; background: #ef444415; }
.empty-cat { font-size: 14px; color: #475569; text-align: center; padding: 24px 0; }
.modal-overlay { position: fixed; inset: 0; background: #00000080; backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: var(--card); transition: background 0.3s; border: 1px solid var(--border2); border-radius: 20px; width: 100%; max-width: 400px; animation: slideUp 0.25s ease; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 24px 24px 0; }
.modal-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--text-heading); }
.modal-close { background: var(--card2); border: 1px solid var(--border2); border-radius: 8px; padding: 6px; color: #64748b; cursor: pointer; display: flex; align-items: center; }
.modal-close:hover { color: #ef4444; }
.modal-body { padding: 24px; display: flex; flex-direction: column; gap: 18px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.form-input { background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; padding: 11px 14px; font-size: 14px; color: var(--text); font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; width: 100%; }
.form-input:focus { border-color: #10b98150; }
.color-picker { display: flex; flex-wrap: wrap; gap: 10px; }
.color-dot { width: 30px; height: 30px; border-radius: 50%; cursor: pointer; transition: transform 0.15s; border: 2px solid transparent; }
.color-dot:hover { transform: scale(1.15); }
.color-dot.selected { border-color: #fff; box-shadow: 0 0 0 3px rgba(255,255,255,0.15); transform: scale(1.1); }
.cat-preview { display: flex; align-items: center; gap: 14px; background: var(--card2); border: 1px solid var(--border2); border-radius: 12px; padding: 14px; }
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

/* ── Responsive Categories ───────────────────────── */
@media (max-width: 1024px) {
  .page-content    { padding: 20px; }
  .categories-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
  .page-content    { padding: 16px; gap: 16px; }
  .page-title      { font-size: 20px; }
  .categories-grid { grid-template-columns: 1fr; }
  .category-card   { padding: 18px; }
}

</style>