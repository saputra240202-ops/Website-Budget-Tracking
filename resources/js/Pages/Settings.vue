<template>
  <AppLayout>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Settings</h1>
          <p class="page-subtitle">Manage your account preferences and settings.</p>
        </div>
      </div>

      <!-- Success Toast -->
      <div v-if="$page.props.flash?.success" class="toast">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        {{ $page.props.flash.success }}
      </div>

      <div class="settings-layout">
        <!-- Tab Sidebar -->
        <div class="tab-sidebar">
          <button v-for="tab in tabs" :key="tab.key" :class="['tab-item', { active: activeTab === tab.key }]" @click="activeTab = tab.key">
            <span class="tab-icon" v-html="tab.icon"></span>
            <span>{{ tab.label }}</span>
          </button>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">

          <!-- PROFILE -->
          <div v-if="activeTab === 'profile'">
            <div class="section-title">Profile Information</div>
            <div class="divider"></div>
            <div class="avatar-row">
              <img class="avatar-img" :src="`https://api.dicebear.com/7.x/initials/svg?seed=${profileForm.name}&backgroundColor=10b981`" alt="avatar"/>
              <div>
                <div class="avatar-name">{{ profileForm.name }}</div>
                <div class="avatar-email">{{ profileForm.email }}</div>
              </div>
            </div>
            <div class="form-row two-col">
              <div class="form-group">
                <label>Full Name</label>
                <input v-model="profileForm.name" type="text" class="form-input"/>
                <span v-if="profileErrors.name" class="form-error">{{ profileErrors.name }}</span>
              </div>
              <div class="form-group">
                <label>Email Address</label>
                <input v-model="profileForm.email" type="email" class="form-input"/>
                <span v-if="profileErrors.email" class="form-error">{{ profileErrors.email }}</span>
              </div>
            </div>
            <div class="divider"></div>
            <div class="section-title">Preferences</div>
            <div class="divider"></div>
            <div class="pref-row">
              <div class="pref-info"><span class="pref-label">Currency</span><span class="pref-desc">Select your default currency.</span></div>
              <select v-model="profileForm.currency" class="form-select">
                <option>IDR (Rp)</option><option>USD ($)</option><option>EUR (€)</option><option>GBP (£)</option><option>JPY (¥)</option>
              </select>
            </div>
            <div class="pref-row">
              <div class="pref-info"><span class="pref-label">Time Zone</span><span class="pref-desc">Your current time zone.</span></div>
              <select v-model="profileForm.timezone" class="form-select">
                <option>UTC</option>
                <option>Pacific Time (PT)</option><option>Mountain Time (MT)</option>
                <option>Central Time (CT)</option><option>Eastern Time (ET)</option>
                <option>Western Indonesia Time (WIB)</option>
              </select>
            </div>
            <div class="form-actions">
              <button class="btn-cancel" @click="resetProfile">Cancel</button>
              <button class="btn-save" @click="saveProfile" :disabled="savingProfile">
                {{ savingProfile ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </div>

          <!-- SECURITY -->
          <div v-if="activeTab === 'security'">
            <div class="section-title">Change Password</div>
            <div class="divider"></div>
            <div class="form-group">
              <label>Current Password</label>
              <div class="input-eye">
                <input :type="showPwd.current ? 'text' : 'password'" v-model="pwdForm.current_password" class="form-input" placeholder="••••••••" autocomplete="current-password"/>
                <button class="eye-btn" @click="showPwd.current = !showPwd.current" v-html="eyeIcon(showPwd.current)"></button>
              </div>
              <span v-if="pwdErrors.current_password" class="form-error">{{ pwdErrors.current_password }}</span>
            </div>
            <div class="form-group">
              <label>New Password</label>
              <div class="input-eye">
                <input :type="showPwd.new ? 'text' : 'password'" v-model="pwdForm.password" class="form-input" placeholder="Min. 8 characters" autocomplete="new-password"/>
                <button class="eye-btn" @click="showPwd.new = !showPwd.new" v-html="eyeIcon(showPwd.new)"></button>
              </div>
              <div class="strength-bar" v-if="pwdForm.password">
                <div class="strength-track"><div class="strength-fill" :style="`width:${pwdStrength.pct}%; background:${pwdStrength.color}`"></div></div>
                <span class="strength-label" :style="`color:${pwdStrength.color}`">{{ pwdStrength.label }}</span>
              </div>
              <span v-if="pwdErrors.password" class="form-error">{{ pwdErrors.password }}</span>
            </div>
            <div class="form-group">
              <label>Confirm New Password</label>
              <div class="input-eye">
                <input :type="showPwd.confirm ? 'text' : 'password'" v-model="pwdForm.password_confirmation" class="form-input" placeholder="••••••••" autocomplete="new-password"/>
                <button class="eye-btn" @click="showPwd.confirm = !showPwd.confirm" v-html="eyeIcon(showPwd.confirm)"></button>
              </div>
              <span v-if="pwdForm.password_confirmation && pwdForm.password !== pwdForm.password_confirmation" class="form-error">Passwords do not match.</span>
            </div>
            <div class="form-actions">
              <button class="btn-cancel" @click="resetPassword">Cancel</button>
              <button class="btn-save" @click="savePassword" :disabled="savingPwd">
                {{ savingPwd ? 'Updating...' : 'Update Password' }}
              </button>
            </div>
          </div>

          <!-- NOTIFICATIONS -->
          <div v-if="activeTab === 'notifications'">
            <div class="section-title">Notification Preferences</div>
            <div class="divider"></div>
            <div class="notif-group-label">Email Notifications</div>
            <div class="toggle-row" v-for="n in emailNotifs" :key="n.key">
              <div class="toggle-info"><span class="toggle-label">{{ n.label }}</span><span class="toggle-desc">{{ n.desc }}</span></div>
              <div :class="['toggle-switch', { on: n.enabled }]" @click="n.enabled = !n.enabled"><div class="toggle-thumb"></div></div>
            </div>
            <div class="divider"></div>
            <div class="notif-group-label">Push Notifications</div>
            <div class="toggle-row" v-for="n in pushNotifs" :key="n.key">
              <div class="toggle-info"><span class="toggle-label">{{ n.label }}</span><span class="toggle-desc">{{ n.desc }}</span></div>
              <div :class="['toggle-switch', { on: n.enabled }]" @click="n.enabled = !n.enabled"><div class="toggle-thumb"></div></div>
            </div>
            <div class="form-actions"><button class="btn-save">Save Changes</button></div>
          </div>

          <!-- APPEARANCE -->
          <div v-if="activeTab === 'appearance'">
            <div class="section-title">Theme</div>
            <div class="divider"></div>
            <div class="theme-options">
              <div
                v-for="t in themes" :key="t.key"
                :class="['theme-card', { selected: appearance.theme === t.key }]"
                @click="previewTheme(t.key)"
              >
                <div class="theme-preview" :style="`background:${t.bg}`">
                  <div class="theme-bar" :style="`background:${t.bar}`"></div>
                  <div class="theme-lines">
                    <div class="theme-line" :style="`background:${t.line}`"></div>
                    <div class="theme-line short" :style="`background:${t.line}`"></div>
                  </div>
                </div>
                <div class="theme-footer">
                  <span class="theme-name">{{ t.label }}</span>
                  <div v-if="appearance.theme === t.key" class="theme-check">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                  </div>
                </div>
              </div>
            </div>

            <div class="divider"></div>
            <div class="section-title">Accent Color</div>
            <div class="divider"></div>
            <div class="accent-options">
              <div
                v-for="c in accentColors" :key="c"
                :class="['accent-dot', { selected: appearance.accent === c }]"
                :style="`background:${c}`"
                @click="appearance.accent = c"
              >
                <svg v-if="appearance.accent === c" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            </div>

            <!-- Preview badge -->
            <div class="preview-badge" v-if="appearance.theme !== savedTheme">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              Preview aktif — klik "Save Changes" untuk menyimpan
            </div>

            <div class="form-actions">
              <button class="btn-cancel" @click="cancelTheme">Cancel</button>
              <button class="btn-save" @click="saveAppearance">Save Changes</button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

export default {
  name: 'Settings',
  components: { AppLayout },
  props: {
    user:   { type: Object, default: () => ({}) },
    errors: { type: Object, default: () => ({}) },
  },
  data() {
    const savedTheme = localStorage.getItem('app-theme') || 'dark'
    return {
      activeTab: 'profile',
      savingProfile: false,
      savingPwd: false,
      savedTheme,
      profileForm: {
        name:     this.user?.name     ?? '',
        email:    this.user?.email    ?? '',
        currency: this.user?.currency ?? 'IDR (Rp)',
        timezone: this.user?.timezone ?? 'Western Indonesia Time (WIB)',
      },
      profileErrors: {},
      pwdForm: { current_password: '', password: '', password_confirmation: '' },
      pwdErrors: {},
      showPwd: { current: false, new: false, confirm: false },
      tabs: [
        { key:'profile',       label:'Profile',       icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>' },
        { key:'security',      label:'Security',      icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>' },
        { key:'notifications', label:'Notifications', icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>' },
        { key:'appearance',    label:'Appearance',    icon:'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>' },
      ],
      emailNotifs: [
        { key:'monthly', label:'Monthly Summary',    desc:'Receive a monthly report of your finances.',       enabled: true  },
        { key:'budget',  label:'Budget Alerts',      desc:'Get notified when you approach your budget limit.', enabled: true  },
        { key:'large',   label:'Large Transactions', desc:'Alert when a transaction exceeds Rp 500.000.',      enabled: false },
      ],
      pushNotifs: [
        { key:'new_tx', label:'New Transaction',      desc:'Push notification for every new transaction.', enabled: true  },
        { key:'goal',   label:'Savings Goal Reached', desc:'Notify when a savings goal is achieved.',      enabled: true  },
        { key:'login',  label:'New Login Alert',      desc:'Alert when your account is accessed.',         enabled: true  },
      ],
      appearance: {
        theme:  localStorage.getItem('app-theme')  || 'dark',
        accent: localStorage.getItem('app-accent') || '#10b981',
      },
      themes: [
        { key:'dark',     label:'Dark',     bg:'#0d1220', bar:'#10b981', line:'#1e293b' },
        { key:'light',    label:'Light',    bg:'#f1f5f9', bar:'#10b981', line:'#cbd5e1' },
        { key:'midnight', label:'Midnight', bg:'#050810', bar:'#6366f1', line:'#1a1f35' },
      ],
      accentColors: ['#10b981','#3b82f6','#8b5cf6','#ec4899','#f97316','#f59e0b','#ef4444','#06b6d4'],
    }
  },
  computed: {
    pwdStrength() {
      const p = this.pwdForm.password
      if (!p) return { pct: 0, label: '', color: '' }
      let s = 0
      if (p.length >= 8) s++; if (/[A-Z]/.test(p)) s++; if (/[0-9]/.test(p)) s++; if (/[^A-Za-z0-9]/.test(p)) s++
      return [
        { pct: 25,  label: 'Weak',   color: '#ef4444' },
        { pct: 50,  label: 'Fair',   color: '#f59e0b' },
        { pct: 75,  label: 'Good',   color: '#3b82f6' },
        { pct: 100, label: 'Strong', color: '#10b981' },
      ][s - 1] || { pct: 25, label: 'Weak', color: '#ef4444' }
    }
  },
  methods: {
    // ── Profile ──────────────────────────────────────────────────
    saveProfile() {
      this.profileErrors = {}
      this.savingProfile = true
      router.put('/settings/profile', this.profileForm, {
        onSuccess: () => { this.savingProfile = false; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Profile berhasil diupdate!', type: 'success' } })) },
        onError:   (errors) => { this.profileErrors = errors; this.savingProfile = false }
      })
    },
    resetProfile() {
      this.profileForm = { name: this.user?.name, email: this.user?.email, currency: this.user?.currency, timezone: this.user?.timezone }
      this.profileErrors = {}
    },

    // ── Password ─────────────────────────────────────────────────
    savePassword() {
      // Validasi frontend
      this.pwdErrors = {}
      if (!this.passwordForm.current_password) { this.pwdErrors.current_password = 'Password lama wajib diisi.'; }
      if (!this.passwordForm.password) { this.pwdErrors.password = 'Password baru wajib diisi.'; }
      else if (this.passwordForm.password.length < 8) { this.pwdErrors.password = 'Password minimal 8 karakter.'; }
      if (this.passwordForm.password !== this.passwordForm.password_confirmation) { this.pwdErrors.password_confirmation = 'Konfirmasi password tidak cocok.'; }
      if (Object.keys(this.pwdErrors).length) return
      if (this.pwdForm.password !== this.pwdForm.password_confirmation) return
      this.pwdErrors = {}
      this.savingPwd = true
      router.put('/settings/password', this.pwdForm, {
        onSuccess: () => { this.resetPassword(); this.savingPwd = false; window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: 'Password berhasil diubah!', type: 'success' } })) },
        onError:   (errors) => { this.pwdErrors = errors; this.savingPwd = false }
      })
    },
    resetPassword() {
      this.pwdForm = { current_password: '', password: '', password_confirmation: '' }
      this.pwdErrors = {}
    },

    // ── Appearance ───────────────────────────────────────────────
    previewTheme(key) {
      // Langsung preview tanpa save dulu
      this.appearance.theme = key
      window.dispatchEvent(new CustomEvent('theme-changed', { detail: key }))
    },
    cancelTheme() {
      // Kembalikan ke tema yang tersimpan
      this.appearance.theme = this.savedTheme
      window.dispatchEvent(new CustomEvent('theme-changed', { detail: this.savedTheme }))
    },
    saveAppearance() {
      // Simpan ke localStorage secara permanen
      localStorage.setItem('app-theme',  this.appearance.theme)
      localStorage.setItem('app-accent', this.appearance.accent)
      this.savedTheme = this.appearance.theme
      window.dispatchEvent(new CustomEvent('theme-changed', { detail: this.appearance.theme }))
    },

    // ── Helper ───────────────────────────────────────────────────
    eyeIcon(show) {
      return show
        ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
        : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'
    }
  }
}
</script>

<style scoped>
.page-content { padding: 32px; display: flex; flex-direction: column; gap: 24px; }
.page-header { animation: fadeUp 0.35s ease both; }
.page-title { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 800; color: var(--text-heading); letter-spacing: -0.01em; }
.page-subtitle { font-size: 14px; color: var(--text-muted); margin-top: 4px; }
.toast { display: flex; align-items: center; gap: 8px; background: #10b98118; border: 1px solid #10b98130; border-radius: 10px; padding: 12px 16px; font-size: 14px; color: #10b981; animation: fadeUp 0.3s ease; }
.settings-layout { display: flex; gap: 20px; animation: fadeUp 0.35s ease 0.08s both; }
.tab-sidebar { width: 190px; flex-shrink: 0; background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 12px; display: flex; flex-direction: column; gap: 2px; align-self: flex-start; }
.tab-item { display: flex; align-items: center; gap: 10px; padding: 11px 12px; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 500; color: var(--text-muted); transition: all 0.18s; background: none; border: 1px solid transparent; font-family: 'DM Sans', sans-serif; text-align: left; }
.tab-item:hover { background: var(--border2); color: var(--text-body); }
.tab-item.active { background: #10b98118; color: #10b981; border-color: #10b98128; }
.tab-icon { display: flex; align-items: center; }
.tab-content { flex: 1; background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 28px; min-width: 0; }
.section-title { font-size: 15px; font-weight: 600; color: var(--text-heading); }
.divider { height: 1px; background: var(--border); margin: 16px 0; }
.avatar-row { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
.avatar-img { width: 56px; height: 56px; border-radius: 14px; border: 2px solid #10b98130; }
.avatar-name { font-size: 15px; font-weight: 600; color: var(--text-heading); }
.avatar-email { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
.form-row { display: flex; gap: 16px; } .form-row.two-col > * { flex: 1; }
.form-group { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; }
.form-group label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
.form-input { background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; padding: 11px 14px; font-size: 14px; color: var(--text); font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.2s; width: 100%; }
.form-input:focus { border-color: #10b98150; }
.form-error { font-size: 12px; color: #ef4444; margin-top: 2px; }
.pref-row { display: flex; align-items: center; justify-content: space-between; gap: 24px; margin-bottom: 16px; }
.pref-info { display: flex; flex-direction: column; gap: 3px; }
.pref-label { font-size: 14px; font-weight: 500; color: var(--text-body); }
.pref-desc { font-size: 12px; color: var(--text-muted); }
.form-select { background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; padding: 10px 36px 10px 14px; font-size: 14px; color: var(--text); font-family: 'DM Sans', sans-serif; outline: none; appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; background-color: var(--card2); min-width: 200px; }
.form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }
.btn-cancel { padding: 10px 20px; background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; color: var(--text-sub); font-size: 14px; font-weight: 500; font-family: 'DM Sans', sans-serif; cursor: pointer; }
.btn-cancel:hover { border-color: var(--text-muted); }
.btn-save { padding: 10px 22px; background: #10b981; border: none; border-radius: 10px; color: #fff; font-size: 14px; font-weight: 600; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: background 0.2s, transform 0.15s; }
.btn-save:hover:not(:disabled) { background: #059669; transform: translateY(-1px); }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
.input-eye { position: relative; display: flex; align-items: center; }
.input-eye .form-input { padding-right: 44px; }
.eye-btn { position: absolute; right: 12px; background: none; border: none; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; padding: 2px; transition: color 0.2s; }
.eye-btn:hover { color: var(--text-sub); }
.strength-bar { display: flex; align-items: center; gap: 10px; margin-top: 6px; }
.strength-track { flex: 1; height: 4px; background: var(--card2); border-radius: 99px; overflow: hidden; }
.strength-fill { height: 100%; border-radius: 99px; transition: width 0.4s, background 0.4s; }
.strength-label { font-size: 12px; font-weight: 600; min-width: 48px; }
.notif-group-label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 12px; }
.toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 14px 0; border-bottom: 1px solid var(--border); }
.toggle-row:last-child { border-bottom: none; }
.toggle-info { display: flex; flex-direction: column; gap: 3px; }
.toggle-label { font-size: 14px; font-weight: 500; color: var(--text-body); }
.toggle-desc { font-size: 12px; color: var(--text-muted); }
.toggle-switch { width: 44px; height: 24px; background: var(--card2); border-radius: 99px; position: relative; cursor: pointer; transition: background 0.25s; flex-shrink: 0; border: 1px solid var(--border2); }
.toggle-switch.on { background: #10b981; border-color: #10b981; }
.toggle-thumb { position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; background: #fff; border-radius: 50%; transition: transform 0.25s ease; }
.toggle-switch.on .toggle-thumb { transform: translateX(20px); }
.theme-options { display: flex; gap: 14px; flex-wrap: wrap; }
.theme-card { cursor: pointer; border-radius: 12px; border: 2px solid var(--border); overflow: hidden; transition: border-color 0.2s, transform 0.15s; width: 130px; }
.theme-card:hover { border-color: #10b98140; transform: translateY(-2px); }
.theme-card.selected { border-color: #10b981; }
.theme-preview { height: 72px; padding: 10px; display: flex; flex-direction: column; gap: 8px; }
.theme-bar { height: 6px; border-radius: 99px; width: 60%; }
.theme-lines { display: flex; flex-direction: column; gap: 5px; }
.theme-line { height: 4px; border-radius: 99px; width: 80%; opacity: 0.5; }
.theme-line.short { width: 50%; }
.theme-footer { display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; background: var(--card); }
.theme-name { font-size: 12px; font-weight: 600; color: var(--text-sub); }
.theme-check { width: 18px; height: 18px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.preview-badge { display: flex; align-items: center; gap: 8px; background: #f59e0b18; border: 1px solid #f59e0b30; border-radius: 10px; padding: 10px 16px; font-size: 13px; color: #f59e0b; margin-top: 20px; }
.accent-options { display: flex; gap: 12px; flex-wrap: wrap; }
.accent-dot { width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.15s; border: 2px solid transparent; }
.accent-dot:hover { transform: scale(1.1); }
.accent-dot.selected { border-color: #fff; box-shadow: 0 0 0 3px rgba(255,255,255,0.15); transform: scale(1.1); }
@keyframes fadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
.input-error { border-color: #ef4444 !important; }
.field-error { font-size: 12px; color: #ef4444; margin-top: 2px; display: block; }

/* ── Responsive Settings ─────────────────────────── */
@media (max-width: 1024px) {
  .page-content    { padding: 20px; }
  .settings-layout { grid-template-columns: 180px 1fr; gap: 16px; }
}
@media (max-width: 768px) {
  .page-content    { padding: 16px; gap: 16px; }
  .page-title      { font-size: 20px; }
  .settings-layout { grid-template-columns: 1fr; }
  .settings-nav    { display: flex; flex-direction: row; overflow-x: auto; gap: 4px; padding: 4px; background: var(--card2); border-radius: 12px; border: none; }
  .settings-nav-item { white-space: nowrap; padding: 8px 14px; border-radius: 8px; border: none; font-size: 13px; }
  .settings-panel  { padding: 16px; }
  .form-row        { grid-template-columns: 1fr; }
}

</style>