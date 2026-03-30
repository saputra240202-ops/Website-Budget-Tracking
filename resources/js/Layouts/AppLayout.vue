<template>
  <div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <div class="logo-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="#10b981" stroke-width="2"/>
            <path d="M12 6v6l4 2" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <span class="logo-text">FinanceApp</span>
      </div>

      <nav class="sidebar-nav">
        <Link
          v-for="item in navItems"
          :key="item.name"
          :href="item.href"
          :class="['nav-item', { active: isActive(item.href) }]"
        >
          <span class="nav-icon" v-html="item.icon"></span>
          <span>{{ item.name }}</span>
        </Link>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
      <!-- Topbar -->
      <header class="topbar">
        <!-- Hamburger (mobile only) -->
        <button class="hamburger" @click="sidebarOpen = !sidebarOpen">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
        <div class="search-bar">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
          <input
            type="text"
            name="search_query_nav"
            placeholder="Search..."
            autocomplete="off"
            autocorrect="off"
            autocapitalize="off"
            spellcheck="false"
          />
        </div>
        <div class="topbar-right">
          <button class="notif-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="notif-dot"></span>
          </button>
          <div class="user-info">
            <div class="avatar-circle">{{ initials }}</div>
            <span class="user-name">{{ shortName }}</span>
          </div>
        </div>
      </header>

      <!-- Page Slot -->
      <main class="page-main">
        <slot />
      </main>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div v-if="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen = false"></div>

    <!-- Mobile Sidebar Drawer -->
    <aside class="sidebar-drawer" :class="{ open: sidebarOpen }">
      <div class="sidebar-logo">
        <div class="logo-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="#10b981" stroke-width="2"/>
            <path d="M12 6v6l4 2" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <span class="logo-text">FinanceApp</span>
        <button class="drawer-close" @click="sidebarOpen = false">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <nav class="sidebar-nav">
        <Link
          v-for="item in navItems"
          :key="item.name"
          :href="item.href"
          :class="['nav-item', { active: isActive(item.href) }]"
          @click="sidebarOpen = false"
        >
          <span class="nav-icon" v-html="item.icon"></span>
          <span>{{ item.name }}</span>
        </Link>
      </nav>
    </aside>

    <!-- Toast Notification -->
    <teleport to="body">
      <div class="toast-container">
        <transition-group name="toast">
          <div
            v-for="toast in toasts"
            :key="toast.id"
            :class="['toast', toast.type]"
          >
            <div class="toast-icon">
              <svg v-if="toast.type === 'success'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <svg v-else-if="toast.type === 'error'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
              <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <span class="toast-msg">{{ toast.message }}</span>
            <button class="toast-close" @click="removeToast(toast.id)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </transition-group>
      </div>
    </teleport>

    <!-- Bottom Nav (mobile only) -->
    <nav class="bottom-nav">
      <Link v-for="item in bottomNavItems" :key="item.name" :href="item.href" :class="['bottom-nav-item', { active: isActive(item.href) }]">
        <span v-html="item.icon"></span>
        <span class="bottom-nav-label">{{ item.name }}</span>
      </Link>
    </nav>

  </div>
</template>

<script>
import { Link, usePage } from '@inertiajs/vue3'

export default {
  name: 'AppLayout',
  components: { Link },
  setup() {
    const page = usePage()
    return { page }
  },
  data() {
    return {
      sidebarOpen: false,
      toasts: [],
      navItems: [
        { name: 'Dashboard',    href: '/',             icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>' },
        { name: 'Transactions', href: '/transactions', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>' },
        { name: 'Categories',   href: '/categories',  icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>' },
        { name: 'Reports',      href: '/reports',     icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>' },
        { name: 'Budget',       href: '/budget',      icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>' },
        { name: 'Tabungan',     href: '/savings',     icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2h0V5z"/><path d="M2 9v1c0 1.1.9 2 2 2h1"/><path d="M16 11h0"/></svg>' },
        { name: 'Settings',     href: '/settings',    icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>' },
      ],
      // Bottom nav: 5 item paling penting untuk mobile
      bottomNavItems: [
        { name: 'Home',     href: '/',             icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>' },
        { name: 'Transaksi', href: '/transactions', icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>' },
        { name: 'Tabungan', href: '/savings',     icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2h0V5z"/><path d="M2 9v1c0 1.1.9 2 2 2h1"/></svg>' },
        { name: 'Budget',   href: '/budget',      icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>' },
        { name: 'Settings', href: '/settings',    icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>' },
      ]
    }
  },
  computed: {
    authUser() {
      return this.page.props.auth.user
    },
    shortName() {
      if (!this.authUser?.name) return ''
      const parts = this.authUser.name.trim().split(' ')
      return parts.length >= 2 ? `${parts[0]} ${parts[parts.length - 1][0]}.` : parts[0]
    },
    initials() {
      if (!this.authUser?.name) return ''
      const parts = this.authUser.name.trim().split(' ')
      return parts.length >= 2
        ? `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase()
        : parts[0].slice(0, 2).toUpperCase()
    },
  },
  mounted() {
    // Apply tema yang tersimpan saat pertama load
    const saved = localStorage.getItem('app-theme') || 'dark'
    this.applyTheme(saved)

    this.toastListener = (e) => {
      this.addToast(e.detail.message, e.detail.type || 'success')
    }

    // Toast event listener — dipanggil dari halaman manapun
    window.addEventListener('show-toast', this.toastListener)

    // Auto-show toast dari Inertia flash (success/error dari backend)
    this.$watch(
      () => this.page.props.flash,
      (flash) => {
        if (flash?.success) this.addToast(flash.success, 'success')
        if (flash?.error)   this.addToast(flash.error,   'error')
      },
      { deep: true, immediate: true }
    )

    this.themeListener = (e) => {
      this.applyTheme(e.detail)
    }

    // Dengarkan event ganti tema dari Settings.vue
    window.addEventListener('theme-changed', this.themeListener)
  },
  beforeUnmount() {
    window.removeEventListener('theme-changed', this.themeListener)
    window.removeEventListener('show-toast', this.toastListener)
  },
  methods: {
    isActive(href) {
      const current = this.page.url
      if (href === '/') return current === '/'
      return current.startsWith(href)
    },
    addToast(message, type = 'success') {
      const id = Date.now() + Math.random()
      this.toasts.push({ id, message, type })
      // Auto-remove setelah 4 detik
      setTimeout(() => this.removeToast(id), 4000)
    },
    removeToast(id) {
      this.toasts = this.toasts.filter(t => t.id !== id)
    },
    applyTheme(theme) {
      document.documentElement.setAttribute('data-theme', theme)
    }
  }
}
</script>

<style>
/* ── CSS Variables per tema ──────────────────────────────────── */
:root,
[data-theme="dark"] {
  --bg:           #080c14;
  --card:         #0d1220;
  --card2:        #131b2e;
  --border:       #ffffff0a;
  --border2:      #ffffff0d;
  --text:         #e2e8f0;
  --text-muted:   #64748b;
  --text-sub:     #94a3b8;
  --text-heading: #f1f5f9;
  --text-body:    #cbd5e1;
}

[data-theme="light"] {
  --bg:           #f1f5f9;
  --card:         #ffffff;
  --card2:        #f8fafc;
  --border:       #e2e8f0;
  --border2:      #e2e8f0;
  --text:         #1e293b;
  --text-muted:   #64748b;
  --text-sub:     #475569;
  --text-heading: #0f172a;
  --text-body:    #334155;
}

[data-theme="midnight"] {
  --bg:           #050810;
  --card:         #080d1a;
  --card2:        #0d1428;
  --border:       #ffffff08;
  --border2:      #ffffff0a;
  --text:         #e2e8f0;
  --text-muted:   #4a5568;
  --text-sub:     #718096;
  --text-heading: #f7fafc;
  --text-body:    #a0aec0;
}

/* ── Fix browser autocomplete ──────────────────────────────── */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-box-shadow: 0 0 0px 1000px var(--card2) inset !important;
  -webkit-text-fill-color: var(--text) !important;
  caret-color: var(--text);
  transition: background-color 9999s ease-in-out 0s;
}
</style>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Syne:wght@700;800&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.app-shell { display: flex; min-height: 100vh; background: var(--bg); font-family: 'DM Sans', sans-serif; color: var(--text); transition: background 0.3s, color 0.3s; }

.sidebar { width: 220px; min-height: 100vh; background: var(--card); border-right: 1px solid var(--border); display: flex; flex-direction: column; padding: 24px 0; position: sticky; top: 0; height: 100vh; flex-shrink: 0; transition: background 0.3s, border-color 0.3s; }

.sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 0 20px 32px; }

.logo-icon { width: 34px; height: 34px; background: #10b98118; border: 1px solid #10b98130; border-radius: 10px; display: flex; align-items: center; justify-content: center; }

.logo-text { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; color: var(--text-heading); letter-spacing: 0.02em; }

.sidebar-nav { display: flex; flex-direction: column; gap: 2px; padding: 0 12px; }

.nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 12px; border-radius: 10px; font-size: 14px; font-weight: 500; color: var(--text-muted); transition: all 0.18s ease; text-decoration: none; border: 1px solid transparent; }

.nav-item:hover { background: var(--border2); color: var(--text-body); }
.nav-item.active { background: #10b98118; color: #10b981; border-color: #10b98128; }
.nav-icon { display: flex; align-items: center; }

.main-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; }

.topbar { display: flex; align-items: center; justify-content: space-between; padding: 16px 32px; background: var(--card); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 10; transition: background 0.3s; }

.search-bar { display: flex; align-items: center; gap: 10px; background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; padding: 9px 16px; color: var(--text-muted); width: 280px; }

.search-bar input { background: none; border: none; outline: none; font-size: 14px; color: var(--text-sub); width: 100%; font-family: 'DM Sans', sans-serif; }
.search-bar input::placeholder { color: var(--text-muted); }

.topbar-right { display: flex; align-items: center; gap: 16px; }

.notif-btn { position: relative; background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; padding: 9px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; transition: border-color 0.2s; }
.notif-btn:hover { border-color: var(--text-muted); }

.notif-dot { position: absolute; top: 8px; right: 8px; width: 7px; height: 7px; background: #ef4444; border-radius: 50%; border: 1.5px solid var(--card); }

.user-info { display: flex; align-items: center; gap: 10px; background: var(--card2); border: 1px solid var(--border2); border-radius: 10px; padding: 7px 14px 7px 7px; cursor: pointer; transition: border-color 0.2s; }
.user-info:hover { border-color: var(--text-muted); }

.avatar-circle { width: 30px; height: 30px; border-radius: 8px; background: #10b981; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }

.user-name { font-size: 14px; font-weight: 500; color: var(--text-body); }

.page-main { flex: 1; }

/* ── TOAST NOTIFICATION ──────────────────────────────────────── */
.toast-container {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
}

.toast {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 13px 16px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  font-family: 'DM Sans', sans-serif;
  min-width: 280px;
  max-width: 380px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.3);
  pointer-events: all;
  backdrop-filter: blur(8px);
}

.toast.success { background: #10b981; color: #fff; }
.toast.error   { background: #ef4444; color: #fff; }
.toast.info    { background: #3b82f6; color: #fff; }

.toast-icon { flex-shrink: 0; display: flex; }
.toast-msg  { flex: 1; line-height: 1.4; }

.toast-close {
  background: rgba(255,255,255,0.2);
  border: none;
  border-radius: 6px;
  padding: 3px;
  cursor: pointer;
  color: #fff;
  display: flex;
  align-items: center;
  flex-shrink: 0;
  pointer-events: all;
}
.toast-close:hover { background: rgba(255,255,255,0.35); }

/* Toast animation */
.toast-enter-active { animation: toastIn 0.3s cubic-bezier(0.34,1.56,0.64,1); }
.toast-leave-active { animation: toastOut 0.25s ease forwards; }
@keyframes toastIn  { from { opacity: 0; transform: translateX(60px) scale(0.9); } to { opacity: 1; transform: translateX(0) scale(1); } }
@keyframes toastOut { from { opacity: 1; transform: translateX(0); max-height: 80px; margin-bottom: 0; } to { opacity: 0; transform: translateX(60px); max-height: 0; margin-bottom: -10px; } }

@media (max-width: 768px) {
  .toast-container { top: auto; bottom: 76px; right: 12px; left: 12px; }
  .toast { min-width: unset; max-width: unset; width: 100%; }
}

/* ── RESPONSIVE ──────────────────────────────────────────────── */
.hamburger { display: none; background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 6px; border-radius: 8px; }
.hamburger:hover { background: var(--card2); }

.sidebar-overlay { display: none; }
.sidebar-drawer  { display: none; }
.bottom-nav      { display: none; }

@media (max-width: 1024px) {
  /* Hide desktop sidebar */
  .sidebar { display: none; }

  /* Show hamburger */
  .hamburger { display: flex; align-items: center; justify-content: center; }

  /* Shrink search bar on tablet */
  .search-bar { width: 200px; }

  /* Overlay */
  .sidebar-overlay {
    display: block;
    position: fixed; inset: 0;
    background: #00000060;
    backdrop-filter: blur(2px);
    z-index: 49;
    animation: fadeIn 0.2s ease;
  }

  /* Drawer */
  .sidebar-drawer {
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0;
    width: 260px; height: 100vh;
    background: var(--card);
    border-right: 1px solid var(--border);
    z-index: 50;
    padding: 24px 0;
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
  }
  .sidebar-drawer.open { transform: translateX(0); }

  .drawer-close {
    background: none; border: none; color: var(--text-muted);
    cursor: pointer; padding: 4px; border-radius: 6px; margin-left: auto;
  }
  .drawer-close:hover { color: #ef4444; }

  /* Topbar padding */
  .topbar { padding: 12px 16px; }

  /* Page main bottom padding for bottom nav */
  .page-main { padding-bottom: 64px; }
}

@media (max-width: 768px) {
  /* Hide search on mobile */
  .search-bar { display: none; }

  /* Show bottom nav */
  .bottom-nav {
    display: flex;
    position: fixed;
    bottom: 0; left: 0; right: 0;
    height: 60px;
    background: var(--card);
    border-top: 1px solid var(--border);
    z-index: 40;
    justify-content: space-around;
    align-items: stretch;
    padding: 0 4px;
  }

  .bottom-nav-item {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 3px; flex: 1; text-decoration: none;
    color: var(--text-muted); font-size: 10px; font-weight: 500;
    padding: 6px 4px; border-radius: 8px; transition: color 0.15s;
  }
  .bottom-nav-item.active { color: #10b981; }
  .bottom-nav-label { font-family: 'DM Sans', sans-serif; }

  /* Shrink topbar */
  .topbar { padding: 10px 12px; }
  .user-name { display: none; }
  .user-info { padding: 7px; }
  .logo-text { font-size: 13px; }
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

</style>