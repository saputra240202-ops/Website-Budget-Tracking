<template>
  <AppLayout>
    <div class="page-content">

      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Tabungan</h1>
          <p class="page-subtitle">Kumpulkan uang sedikit demi sedikit untuk tujuan masa depan.</p>
        </div>
        <button class="btn-add" @click="openSavingModal()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Buat Tabungan
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="summary-row">
        <div class="summary-card" style="--delay:0s">
          <div class="summary-icon" style="background:#10b98118;border-color:#10b98130">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M12 2a10 10 0 1 0 10 10H12V2z"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
          </div>
          <div>
            <div class="summary-label">Total Terkumpul</div>
            <div class="summary-value">Rp {{ summary.totalSaved }}</div>
          </div>
        </div>
        <div class="summary-card" style="--delay:0.06s">
          <div class="summary-icon" style="background:#3b82f618;border-color:#3b82f630">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div>
            <div class="summary-label">Total Target Aktif</div>
            <div class="summary-value">Rp {{ summary.totalTarget }}</div>
          </div>
        </div>
        <div class="summary-card" style="--delay:0.12s">
          <div class="summary-icon" style="background:#8b5cf618;border-color:#8b5cf630">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <div>
            <div class="summary-label">Tabungan Tercapai</div>
            <div class="summary-value">{{ summary.totalDone }} tabungan</div>
          </div>
        </div>
        <div class="summary-card" style="--delay:0.18s">
          <div class="summary-icon" style="background:#f59e0b18;border-color:#f59e0b30">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div>
            <div class="summary-label">Progress Keseluruhan</div>
            <div class="summary-value">{{ summary.activePct }}%</div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="savings.length === 0" class="empty-wrap">
        <div class="empty-icon">🐷</div>
        <h3 class="empty-title">Belum ada tabungan</h3>
        <p class="empty-desc">Mulai buat tabungan pertamamu dan sisihkan uang untuk tujuan masa depan.</p>
        <button class="btn-add" @click="openSavingModal()">+ Buat Tabungan Pertama</button>
      </div>

      <!-- Saving Cards Grid -->
      <div v-else class="savings-grid">
        <div
          v-for="(item, i) in savings" :key="item.id"
          class="saving-card"
          :class="{ completed: item.status === 'completed', cancelled: item.status === 'cancelled' }"
          :style="`--delay: ${i * 0.07}s`"
        >
          <!-- Card Header -->
          <div class="card-top">
            <div class="card-icon" :style="`background: ${item.color}20; border: 1px solid ${item.color}40`">
              <span class="card-emoji">{{ item.icon }}</span>
            </div>
            <div class="card-info">
              <div class="card-name">{{ item.name }}</div>
              <div class="card-status-row">
                <span :class="['status-pill', item.status]">
                  {{ item.status === 'active' ? 'Aktif' : item.status === 'completed' ? '✓ Tercapai' : 'Dibatalkan' }}
                </span>
                <span v-if="item.deadline_label" class="deadline-label">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  {{ item.deadline_label }}
                </span>
              </div>
            </div>
            <div class="card-menu">
              <button class="icon-btn" @click="openSavingModal(item)" title="Edit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="icon-btn danger" @click="openDeleteConfirm(item)" title="Hapus">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              </button>
            </div>
          </div>

          <!-- Progress -->
          <div class="progress-section">
            <div class="progress-amounts">
              <span class="amount-current">Rp {{ item.current_amount }}</span>
              <span class="amount-target">/ Rp {{ item.target_amount }}</span>
            </div>
            <div class="progress-track">
              <div class="progress-bar" :style="`width: ${item.pct}%; background: ${item.color}`"></div>
            </div>
            <div class="progress-footer">
              <span class="pct-text" :style="`color: ${item.color}`">{{ item.pct }}%</span>
              <span v-if="item.status === 'active'" class="remaining-text">Sisa: Rp {{ item.remaining }}</span>
              <span v-if="item.monthly_needed" class="monthly-text">≈ Rp {{ item.monthly_needed }}/bln</span>
            </div>
          </div>

          <!-- Notes -->
          <p v-if="item.notes" class="card-notes">{{ item.notes }}</p>

          <!-- Actions -->
          <div v-if="item.status === 'active'" class="card-actions">
            <button class="btn-deposit" @click="openTransactionModal(item, 'deposit')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Setor
            </button>
            <button class="btn-withdraw" @click="openTransactionModal(item, 'withdrawal')" :disabled="item.current_raw <= 0">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Tarik
            </button>
            <button class="btn-history" @click="openHistory(item)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.62"/></svg>
              Riwayat
            </button>
          </div>
          <div v-else class="card-actions">
            <button class="btn-history" @click="openHistory(item)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.62"/></svg>
              Lihat Riwayat
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── MODAL: Buat / Edit Tabungan ─────────────────────────── -->
    <div v-if="showSavingModal" class="modal-overlay" @click.self="closeSavingModal">
      <div class="modal">
        <div class="modal-header">
          <h2 class="modal-title">{{ editMode ? 'Edit Tabungan' : 'Buat Tabungan Baru' }}</h2>
          <button class="modal-close" @click="closeSavingModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="modal-body">
          <!-- Icon & Color picker -->
          <div class="icon-row">
            <div class="form-group" style="flex:0 0 auto">
              <label>Icon</label>
              <div class="icon-picker">
                <button
                  v-for="e in iconOptions" :key="e"
                  :class="['icon-opt', { selected: savingForm.icon === e }]"
                  @click="savingForm.icon = e"
                >{{ e }}</button>
              </div>
            </div>
            <div class="form-group" style="flex:0 0 auto">
              <label>Warna</label>
              <div class="color-picker">
                <div
                  v-for="c in colorOptions" :key="c"
                  :class="['color-dot', { selected: savingForm.color === c }]"
                  :style="`background:${c}`"
                  @click="savingForm.color = c"
                ></div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Nama Tabungan</label>
            <input v-model="savingForm.name" type="text" :class="['form-input', savingFormErrors.name ? 'input-error' : '']" placeholder="Cth: Beli Laptop, Dana Liburan" @input="savingFormErrors.name = ''"/>
            <span v-if="savingFormErrors.name" class="field-error">{{ savingFormErrors.name }}</span>
          </div>
          <div class="form-group">
            <label>Target (Rp)</label>
            <input v-model="savingForm.target_amount" type="number" :class="['form-input', savingFormErrors.target_amount ? 'input-error' : '']" placeholder="5000000" min="1"/>
            <span v-if="savingFormErrors.target_amount" class="field-error">{{ savingFormErrors.target_amount }}</span>
          </div>
          <div class="form-group">
            <label>Deadline <span class="opt">(opsional)</span></label>
            <input v-model="savingForm.deadline" type="date" class="form-input"/>
          </div>
          <div class="form-group">
            <label>Catatan <span class="opt">(opsional)</span></label>
            <input v-model="savingForm.notes" type="text" class="form-input" placeholder="Cth: Untuk laptop kerja baru"/>
          </div>

          <!-- Preview -->
          <div class="preview-card" :style="`border-color: ${savingForm.color}40`">
            <span class="preview-emoji">{{ savingForm.icon }}</span>
            <div>
              <div class="preview-name">{{ savingForm.name || 'Nama Tabungan' }}</div>
              <div class="preview-target">Target: Rp {{ savingForm.target_amount ? Number(savingForm.target_amount).toLocaleString('id-ID') : '0' }}</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="closeSavingModal">Batal</button>
          <button class="btn-save" @click="saveSaving" :disabled="saving">
            {{ saving ? 'Menyimpan...' : (editMode ? 'Simpan Perubahan' : 'Buat Tabungan') }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── MODAL: Setor / Tarik ─────────────────────────────────── -->
    <div v-if="showTxModal" class="modal-overlay" @click.self="closeTxModal">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h2 class="modal-title">
            {{ txForm.type === 'deposit' ? '💰 Setor ke Tabungan' : '💸 Tarik dari Tabungan' }}
          </h2>
          <button class="modal-close" @click="closeTxModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="modal-body">
          <div class="tx-saving-info">
            <span class="tx-emoji">{{ txForm.savingIcon }}</span>
            <div>
              <div class="tx-saving-name">{{ txForm.savingName }}</div>
              <div class="tx-saving-balance">
                Saldo tabungan: <strong>Rp {{ txForm.currentAmount }}</strong>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Jumlah (Rp)</label>
            <input v-model="txForm.amount" type="number" :class="['form-input', txFormErrors.amount ? 'input-error' : '']" placeholder="0" min="1" :max="txForm.type === 'withdrawal' ? txForm.currentRaw : undefined" @input="txFormErrors.amount = ''"/>
            <span v-if="txFormErrors.amount" class="field-error">{{ txFormErrors.amount }}</span>
          </div>
          <div v-if="txForm.type === 'withdrawal' && txForm.amount > txForm.currentRaw" class="error-msg">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Melebihi saldo tabungan
          </div>
          <div class="form-group">
            <label>Catatan <span class="opt">(opsional)</span></label>
            <input v-model="txForm.note" type="text" class="form-input" placeholder="Cth: Gajian bulan ini"/>
          </div>
          <div class="tx-info-box" :class="txForm.type">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span v-if="txForm.type === 'deposit'">Setoran akan otomatis mengurangi saldo utama di Dashboard.</span>
            <span v-else>Penarikan akan otomatis menambah saldo utama di Dashboard.</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="closeTxModal">Batal</button>
          <button
            :class="txForm.type === 'deposit' ? 'btn-save' : 'btn-withdraw-confirm'"
            @click="submitTransaction"
            :disabled="saving || !txForm.amount || txForm.amount <= 0 || (txForm.type === 'withdrawal' && txForm.amount > txForm.currentRaw)"
          >
            {{ saving ? 'Memproses...' : (txForm.type === 'deposit' ? 'Setor Sekarang' : 'Tarik Sekarang') }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── MODAL: Riwayat Setoran ───────────────────────────────── -->
    <div v-if="showHistoryModal" class="modal-overlay" @click.self="closeHistory">
      <div class="modal modal-history">
        <div class="modal-header">
          <h2 class="modal-title">Riwayat — {{ historyItem?.name }}</h2>
          <button class="modal-close" @click="closeHistory">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="modal-body history-body">
          <div v-if="historyLoading" class="history-loading">Memuat...</div>
          <div v-else-if="historyList.length === 0" class="history-empty">Belum ada riwayat.</div>
          <div v-else class="history-list">
            <div v-for="h in historyList" :key="h.id" class="history-item">
              <div :class="['history-dot', h.type]"></div>
              <div class="history-detail">
                <div class="history-amount" :class="h.type">
                  {{ h.type === 'deposit' ? '+' : '-' }}Rp {{ h.amount }}
                </div>
                <div class="history-note">{{ h.note || (h.type === 'deposit' ? 'Setoran' : 'Penarikan') }}</div>
              </div>
              <div class="history-date">{{ h.created_at }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── MODAL: Delete Confirm ────────────────────────────────── -->
    <div v-if="deleteConfirm.show" class="modal-overlay" @click.self="cancelDelete">
      <div class="modal delete-modal">
        <div class="delete-icon-wrap">
          <div class="delete-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          </div>
        </div>
        <div class="delete-body">
          <h3 class="delete-title">Hapus Tabungan?</h3>
          <p class="delete-desc">Tabungan <strong>"{{ deleteConfirm.name }}"</strong> akan dihapus permanen. Transaksi setor/tarik yang sudah ada tidak akan ikut terhapus.</p>
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
  name: 'Savings',
  components: { AppLayout },
  props: {
    savings: { type: Array,  default: () => [] },
    summary: { type: Object, default: () => ({}) },
  },
  data() {
    return {
      // ── Saving CRUD modal ──
      showSavingModal: false,
      savingFormErrors: {},
      txFormErrors: {},
      editMode:        false,
      saving:          false,
      savingForm: {
        id: null, name: '', target_amount: '', deadline: '',
        color: '#10b981', icon: '🎯', notes: '',
      },

      // ── Deposit / Withdraw modal ──
      showTxModal: false,
      txForm: {
        savingId: null, savingName: '', savingIcon: '',
        currentAmount: '0', currentRaw: 0,
        type: 'deposit', amount: '', note: '',
      },

      // ── History modal ──
      showHistoryModal: false,
      historyItem:      null,
      historyList:      [],
      historyLoading:   false,

      // ── Delete confirm ──
      deleteConfirm: { show: false, name: '', id: null, loading: false },

      iconOptions:  ['🎯','💻','✈️','🏠','🚗','📱','👗','🎓','💍','🌴','🎮','🏋️','🐶','💊','🛒','🎁'],
      colorOptions: ['#10b981','#3b82f6','#8b5cf6','#ec4899','#f59e0b','#ef4444','#06b6d4','#84cc16','#f97316','#6366f1'],
    }
  },
  methods: {
    // ── Saving modal ──────────────────────────────────────────────
    openSavingModal(item = null) {
      if (item) {
        this.editMode = true
        this.savingForm = {
          id:            item.id,
          name:          item.name,
          target_amount: item.target_raw,
          deadline:      item.deadline ?? '',
          color:         item.color,
          icon:          item.icon,
          notes:         item.notes ?? '',
        }
      } else {
        this.editMode = false
        this.savingForm = { id: null, name: '', target_amount: '', deadline: '', color: '#10b981', icon: '🎯', notes: '' }
      }
      this.savingFormErrors = {}
      this.showSavingModal = true
    },
    closeSavingModal() { this.showSavingModal = false; this.savingFormErrors = {} },
    saveSaving() {
      this.savingFormErrors = {}
      if (!this.savingForm.name.trim()) this.savingFormErrors.name = 'Nama tabungan wajib diisi.'
      if (!this.savingForm.target_amount || Number(this.savingForm.target_amount) <= 0) this.savingFormErrors.target_amount = 'Target harus lebih dari 0.'
      if (Object.keys(this.savingFormErrors).length) return
      this.saving = true
      const data = { ...this.savingForm }
      if (!data.deadline) data.deadline = null

      if (this.editMode) {
        router.put(`/savings/${this.savingForm.id}`, data, {
          onSuccess: () => { this.closeSavingModal(); this.saving = false; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Tabungan berhasil diupdate!', type: 'success' } })) },
          onError: (e) => { this.savingFormErrors = e; this.saving = false; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Gagal menyimpan tabungan.', type: 'error' } })) },
        })
      } else {
        router.post('/savings', data, {
          onSuccess: () => { this.closeSavingModal(); this.saving = false; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Tabungan berhasil dibuat!', type: 'success' } })) },
          onError: (e) => { this.savingFormErrors = e; this.saving = false; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Gagal menyimpan tabungan.', type: 'error' } })) },
        })
      }
    },

    // ── Deposit / Withdraw modal ──────────────────────────────────
    openTransactionModal(item, type) {
      this.txForm = {
        savingId:      item.id,
        savingName:    item.name,
        savingIcon:    item.icon,
        currentAmount: item.current_amount,
        currentRaw:    item.current_raw,
        type,
        amount: '',
        note:   '',
      }
      this.showTxModal = true
    },
    closeTxModal() { this.showTxModal = false },
    submitTransaction() {
      this.txFormErrors = {}
      if (!this.txForm.amount || Number(this.txForm.amount) <= 0) { this.txFormErrors.amount = 'Jumlah harus lebih dari 0.'; return }
      if (this.txForm.type === 'withdrawal' && Number(this.txForm.amount) > this.txForm.currentRaw) { this.txFormErrors.amount = 'Melebihi saldo tabungan.'; return }
      this.saving = true
      const endpoint = this.txForm.type === 'deposit'
        ? `/savings/${this.txForm.savingId}/deposit`
        : `/savings/${this.txForm.savingId}/withdraw`

      router.post(endpoint, { amount: this.txForm.amount, note: this.txForm.note }, {
        onSuccess: () => { this.closeTxModal(); this.saving = false; const msg = this.txForm.type === 'deposit' ? 'Setoran berhasil!' : 'Penarikan berhasil!'; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: msg, type: 'success' } })) },
        onError: () => { this.saving = false; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Gagal, coba lagi.', type: 'error' } })) },
      })
    },

    // ── History modal ─────────────────────────────────────────────
    async openHistory(item) {
      this.historyItem      = item
      this.showHistoryModal = true
      this.historyLoading   = true
      this.historyList      = []
      try {
        const res  = await fetch(`/savings/${item.id}/deposits`)
        this.historyList = await res.json()
      } catch (e) {
        this.historyList = []
      } finally {
        this.historyLoading = false
      }
    },
    closeHistory() { this.showHistoryModal = false; this.historyItem = null; this.historyList = [] },

    // ── Delete confirm ────────────────────────────────────────────
    openDeleteConfirm(item) {
      this.deleteConfirm = { show: true, name: item.name, id: item.id, loading: false }
    },
    cancelDelete() {
      this.deleteConfirm = { show: false, name: '', id: null, loading: false }
    },
    confirmDelete() {
      this.deleteConfirm.loading = true
      router.delete(`/savings/${this.deleteConfirm.id}`, {
        onSuccess: () => { this.cancelDelete(); window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Tabungan berhasil dihapus.', type: 'success' } })) },
        onError:   () => { this.deleteConfirm.loading = false },
      })
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

/* Summary */
.summary-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; animation: fadeUp 0.35s ease 0.06s both; }
.summary-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 18px 20px; display: flex; align-items: center; gap: 16px; animation: fadeUp 0.4s ease both; animation-delay: var(--delay); transition: border-color 0.2s, transform 0.2s; }
.summary-card:hover { border-color: #10b98130; transform: translateY(-2px); }
.summary-icon { width: 44px; height: 44px; border-radius: 12px; border: 1px solid transparent; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.summary-label { font-size: 12px; color: #64748b; font-weight: 500; }
.summary-value { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--text-heading); margin-top: 2px; }

/* Empty */
.empty-wrap { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 60px 0; }
.empty-icon { font-size: 56px; }
.empty-title { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 700; color: var(--text-heading); }
.empty-desc { font-size: 14px; color: #64748b; text-align: center; max-width: 340px; }

/* Saving Cards Grid */
.savings-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; animation: fadeUp 0.35s ease 0.1s both; }
.saving-card { background: var(--card); border: 1px solid var(--border); border-radius: 18px; padding: 22px; display: flex; flex-direction: column; gap: 16px; animation: fadeUp 0.4s ease both; animation-delay: var(--delay); transition: border-color 0.2s, transform 0.2s; }
.saving-card:hover { transform: translateY(-2px); border-color: #ffffff18; }
.saving-card.completed { border-color: #10b98130; }
.saving-card.cancelled { opacity: 0.55; }

/* Card Top */
.card-top { display: flex; align-items: flex-start; gap: 12px; }
.card-icon { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.card-emoji { font-size: 22px; }
.card-info { flex: 1; min-width: 0; }
.card-name { font-size: 15px; font-weight: 700; color: var(--text-heading); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.card-status-row { display: flex; align-items: center; gap: 8px; margin-top: 4px; flex-wrap: wrap; }
.status-pill { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
.status-pill.active    { background: #10b98118; color: #10b981; border: 1px solid #10b98130; }
.status-pill.completed { background: #3b82f618; color: #3b82f6; border: 1px solid #3b82f630; }
.status-pill.cancelled { background: #64748b18; color: #64748b; border: 1px solid #64748b30; }
.deadline-label { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #64748b; }
.card-menu { display: flex; gap: 4px; flex-shrink: 0; }
.icon-btn { background: none; border: none; padding: 6px; border-radius: 7px; cursor: pointer; color: #475569; transition: all 0.15s; }
.icon-btn:hover       { color: #3b82f6; background: #3b82f615; }
.icon-btn.danger:hover { color: #ef4444; background: #ef444415; }

/* Progress */
.progress-section { display: flex; flex-direction: column; gap: 8px; }
.progress-amounts { display: flex; align-items: baseline; gap: 4px; }
.amount-current { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; color: var(--text-heading); }
.amount-target { font-size: 13px; color: #64748b; }
.progress-track { height: 8px; background: var(--card2); border-radius: 99px; overflow: hidden; }
.progress-bar { height: 100%; border-radius: 99px; transition: width 0.6s ease; }
.progress-footer { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.pct-text { font-size: 13px; font-weight: 700; }
.remaining-text { font-size: 12px; color: #64748b; }
.monthly-text { font-size: 12px; color: #f59e0b; margin-left: auto; }

/* Notes */
.card-notes { font-size: 13px; color: #64748b; line-height: 1.5; margin: 0; padding: 10px 12px; background: var(--card2); border-radius: 8px; }

/* Action buttons */
.card-actions { display: flex; gap: 8px; }
.btn-deposit  { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 0; border-radius: 10px; border: none; background: #10b98120; color: #10b981; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.15s; }
.btn-deposit:hover { background: #10b98130; }
.btn-withdraw { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 0; border-radius: 10px; border: none; background: #ef444420; color: #ef4444; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.15s; }
.btn-withdraw:hover:not(:disabled) { background: #ef444430; }
.btn-withdraw:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-history  { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 0; border-radius: 10px; border: 1px solid var(--border2); background: none; color: #64748b; font-size: 13px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: all 0.15s; }
.btn-history:hover { color: var(--text-body); border-color: #ffffff30; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: #00000080; backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 100; animation: fadeIn 0.2s ease; }
.modal { background: var(--card); border: 1px solid var(--border2); border-radius: 20px; width: 100%; max-width: 480px; animation: slideUp 0.25s ease; max-height: 90vh; overflow-y: auto; }
.modal-sm { max-width: 400px; }
.modal-history { max-width: 440px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 24px 24px 0; position: sticky; top: 0; background: var(--card); z-index: 1; }
.modal-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--text-heading); }
.modal-close { background: var(--card2); border: 1px solid var(--border2); border-radius: 8px; padding: 6px; color: #64748b; cursor: pointer; display: flex; align-items: center; }
.modal-close:hover { color: #ef4444; }
.modal-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 0 24px 24px; }

/* Form */
.form-group { display: flex; flex-direction: column; gap: 7px; }
.form-group label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.opt { font-size: 11px; color: #475569; text-transform: none; font-weight: 400; }
.form-input { background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; padding: 11px 14px; font-size: 14px; color: var(--text); font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; width: 100%; }
.form-input:focus { border-color: #10b98150; }

/* Icon & Color picker */
.icon-row { display: flex; gap: 20px; flex-wrap: wrap; }
.icon-picker { display: flex; flex-wrap: wrap; gap: 6px; max-width: 260px; }
.icon-opt { width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--border2); background: var(--card2); font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
.icon-opt:hover    { border-color: #10b98150; transform: scale(1.1); }
.icon-opt.selected { border-color: #10b981; background: #10b98120; box-shadow: 0 0 0 2px #10b98130; }
.color-picker { display: flex; flex-wrap: wrap; gap: 8px; }
.color-dot { width: 28px; height: 28px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: transform 0.15s; }
.color-dot:hover    { transform: scale(1.15); }
.color-dot.selected { border-color: #fff; box-shadow: 0 0 0 3px rgba(255,255,255,0.15); transform: scale(1.1); }

/* Preview */
.preview-card { display: flex; align-items: center; gap: 14px; background: var(--card2); border: 1px solid; border-radius: 12px; padding: 14px; }
.preview-emoji { font-size: 28px; }
.preview-name { font-size: 15px; font-weight: 600; color: var(--text-heading); }
.preview-target { font-size: 13px; color: #64748b; margin-top: 2px; }

/* Tx modal */
.tx-saving-info { display: flex; align-items: center; gap: 12px; background: var(--card2); border-radius: 12px; padding: 14px; }
.tx-emoji { font-size: 28px; }
.tx-saving-name { font-size: 14px; font-weight: 600; color: var(--text-heading); }
.tx-saving-balance { font-size: 13px; color: #64748b; margin-top: 2px; }
.tx-saving-balance strong { color: #10b981; }
.tx-info-box { display: flex; align-items: flex-start; gap: 8px; padding: 12px 14px; border-radius: 10px; font-size: 13px; line-height: 1.5; }
.tx-info-box.deposit   { background: #10b98112; color: #10b981; border: 1px solid #10b98125; }
.tx-info-box.withdrawal { background: #ef444412; color: #ef4444; border: 1px solid #ef444425; }
.error-msg { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #ef4444; }

/* Buttons */
.btn-cancel { padding: 10px 20px; background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; color: var(--text-sub); font-size: 14px; font-weight: 500; font-family: 'DM Sans', sans-serif; cursor: pointer; }
.btn-save { padding: 10px 22px; background: #10b981; border: none; border-radius: 10px; color: #fff; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.2s, transform 0.15s; }
.btn-save:hover:not(:disabled) { background: #059669; transform: translateY(-1px); }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-withdraw-confirm { padding: 10px 22px; background: #ef4444; border: none; border-radius: 10px; color: #fff; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.2s; }
.btn-withdraw-confirm:hover:not(:disabled) { background: #dc2626; }
.btn-withdraw-confirm:disabled { opacity: 0.6; cursor: not-allowed; }

/* History */
.history-body { max-height: 380px; overflow-y: auto; }
.history-loading, .history-empty { text-align: center; font-size: 14px; color: #64748b; padding: 24px 0; }
.history-list { display: flex; flex-direction: column; gap: 2px; }
.history-item { display: flex; align-items: center; gap: 12px; padding: 12px 4px; border-bottom: 1px solid var(--border); }
.history-item:last-child { border-bottom: none; }
.history-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.history-dot.deposit    { background: #10b981; }
.history-dot.withdrawal { background: #ef4444; }
.history-detail { flex: 1; }
.history-amount { font-size: 15px; font-weight: 700; }
.history-amount.deposit    { color: #10b981; }
.history-amount.withdrawal { color: #ef4444; }
.history-note { font-size: 12px; color: #64748b; margin-top: 2px; }
.history-date { font-size: 12px; color: #475569; white-space: nowrap; }

/* Delete modal */
.delete-modal { max-width: 380px !important; }
.delete-icon-wrap { display: flex; justify-content: center; padding: 32px 24px 0; }
.delete-icon { width: 64px; height: 64px; border-radius: 50%; background: #ef444415; border: 1px solid #ef444430; display: flex; align-items: center; justify-content: center; }
.delete-body { padding: 20px 28px 8px; text-align: center; }
.delete-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--text-heading); margin-bottom: 8px; }
.delete-desc { font-size: 14px; color: #64748b; line-height: 1.6; }
.delete-footer { display: flex; gap: 10px; padding: 24px 28px 28px; }
.delete-footer .btn-cancel { flex: 1; text-align: center; }
.btn-delete { flex: 1; display: flex; align-items: center; justify-content: center; gap: 7px; padding: 10px 20px; background: #ef4444; border: none; border-radius: 10px; color: #fff; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.2s; }
.btn-delete:hover:not(:disabled) { background: #dc2626; }
.btn-delete:disabled { opacity: 0.6; cursor: not-allowed; }
@keyframes spin { to { transform: rotate(360deg); } }
.spin-icon { animation: spin 0.7s linear infinite; }

@keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeUp  { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

.input-error { border-color: #ef4444 !important; }
.field-error { font-size: 12px; color: #ef4444; margin-top: 2px; display: block; }

/* ── Responsive Savings ───────────────────────────── */
@media (max-width: 1024px) {
  .page-content  { padding: 20px; }
  .summary-row   { grid-template-columns: repeat(2, 1fr); }
  .savings-grid  { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .page-content  { padding: 16px; gap: 16px; }
  .page-title    { font-size: 20px; }
  .page-header   { flex-wrap: wrap; gap: 10px; }
  .summary-row   { grid-template-columns: 1fr 1fr; gap: 10px; }
  .summary-card  { padding: 14px 16px; gap: 12px; }
  .summary-value { font-size: 16px; }
  .savings-grid  { grid-template-columns: 1fr; }
  .saving-card   { padding: 18px; }
  .amount-current { font-size: 18px; }
  /* Modal full screen on mobile */
  .modal         { max-width: 100% !important; margin: 0; border-radius: 20px 20px 0 0; position: fixed; bottom: 0; left: 0; right: 0; max-height: 90vh; }
  .modal-overlay { align-items: flex-end; }
}
@media (max-width: 480px) {
  .summary-row   { grid-template-columns: 1fr; }
}

</style>