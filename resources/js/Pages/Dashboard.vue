<template>
  <AppLayout>
    <div class="page-content">

      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Dashboard</h1>
          <p class="page-subtitle">Welcome back, {{ $page.props.auth.user.name }} 👋</p>
        </div>
        <div class="header-date">{{ today }}</div>
      </div>

      <!-- Active Month Banner -->
      <div class="active-month-banner" v-if="stats.activeMonth">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Menampilkan data bulan <strong>{{ stats.activeMonth }}</strong> (dari transaksi terbaru)
      </div>

      <!-- Stat Cards -->
      <div class="stat-cards">
        <div class="stat-card" v-for="(card, i) in statCards" :key="i" :style="`--delay: ${i * 0.08}s`">
          <div class="card-top">
            <span class="card-label">{{ card.label }}</span>
            <div class="card-icon" :style="`background: ${card.iconBg}`">
              <span v-html="card.icon"></span>
            </div>
          </div>
          <div class="card-value">Rp {{ card.value }}</div>
          <div class="card-change" :class="card.changeClass">{{ card.change }}</div>
        </div>
      </div>

      <!-- Charts Row: Income vs Expense + Donut -->
      <div class="charts-row">
        <div class="chart-card line-card">
          <div class="chart-header">
            <h3 class="chart-title">Income vs Expense</h3>
            <div class="chart-legend">
              <span class="legend-dot" style="background:#10b981"></span><span>Income</span>
              <span class="legend-dot" style="background:#ef4444; margin-left:12px;"></span><span>Expense</span>
            </div>
          </div>
          <div v-if="isChartEmpty" class="chart-empty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#334155" stroke-width="1.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
            <p>Belum ada data transaksi.</p>
          </div>
          <div v-else class="chart-wrap" ref="chartWrap" @mousemove="onChartHover" @mouseleave="tooltip.visible = false">
            <svg class="line-svg" :viewBox="`0 0 ${lW} ${lH}`" preserveAspectRatio="none">
              <defs>
                <linearGradient id="incomeGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#10b981" stop-opacity="0.25"/><stop offset="100%" stop-color="#10b981" stop-opacity="0"/></linearGradient>
                <linearGradient id="expenseGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#ef4444" stop-opacity="0.15"/><stop offset="100%" stop-color="#ef4444" stop-opacity="0"/></linearGradient>
              </defs>
              <line v-for="g in yGrid" :key="g" :x1="yPad" :y1="yScale(g)" :x2="lW" :y2="yScale(g)" stroke="#ffffff08" stroke-width="1"/>
              <path :d="areaPath('income')" fill="url(#incomeGrad)"/>
              <path :d="areaPath('expense')" fill="url(#expenseGrad)"/>
              <polyline :points="linePath('income')"  fill="none" stroke="#10b981" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
              <polyline :points="linePath('expense')" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
              <line v-if="tooltip.visible" :x1="tooltip.svgX" y1="0" :x2="tooltip.svgX" :y2="lH-18" stroke="#ffffff15" stroke-width="1.5" stroke-dasharray="4 3"/>
              <circle v-for="(pt, i) in linePoints('income')"  :key="'ni'+i" :cx="pt.x" :cy="pt.y" r="4" fill="#10b981" stroke="#080c14" stroke-width="2" :opacity="tooltip.visible && tooltip.index===i ? 0 : 1"/>
              <circle v-for="(pt, i) in linePoints('expense')" :key="'ne'+i" :cx="pt.x" :cy="pt.y" r="4" fill="#ef4444" stroke="#080c14" stroke-width="2" :opacity="tooltip.visible && tooltip.index===i ? 0 : 1"/>
              <template v-if="tooltip.visible && tooltip.index >= 0">
                <circle :cx="linePoints('income')[tooltip.index].x"  :cy="linePoints('income')[tooltip.index].y"  r="6" fill="#10b981" stroke="#080c14" stroke-width="2.5"/>
                <circle :cx="linePoints('expense')[tooltip.index].x" :cy="linePoints('expense')[tooltip.index].y" r="6" fill="#ef4444" stroke="#080c14" stroke-width="2.5"/>
              </template>
              <text v-for="(pt, i) in linePoints('income')" :key="'l'+i" :x="pt.x" :y="lH-2" fill="#475569" font-size="11" text-anchor="middle" :font-weight="tooltip.visible && tooltip.index===i ? '700' : '400'">{{ chartData[i].month }}</text>
            </svg>
            <div v-if="tooltip.visible" class="chart-tooltip" :style="tooltipStyle">
              <div class="tooltip-month">{{ tooltip.month }}</div>
              <div class="tooltip-row"><span class="tooltip-dot" style="background:#10b981"></span><span class="tooltip-label">Income</span><span class="tooltip-val income">Rp {{ tooltip.income }}</span></div>
              <div class="tooltip-row"><span class="tooltip-dot" style="background:#ef4444"></span><span class="tooltip-label">Expense</span><span class="tooltip-val expense">Rp {{ tooltip.expense }}</span></div>
              <div class="tooltip-divider"></div>
              <div class="tooltip-row"><span class="tooltip-label">Selisih</span><span class="tooltip-val" :class="tooltip.diffPositive ? 'income' : 'expense'">{{ tooltip.diffPositive ? '+' : '' }}Rp {{ tooltip.diff }}</span></div>
            </div>
          </div>
        </div>

        <div class="chart-card donut-card">
          <div class="chart-header"><h3 class="chart-title">Pengeluaran per Kategori</h3></div>
          <div v-if="expenseByCategory.length === 0" class="chart-empty">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#334155" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <p>Belum ada pengeluaran bulan ini.</p>
          </div>
          <template v-else>
            <div class="donut-wrap">
              <svg viewBox="0 0 180 180" class="donut-svg">
                <circle cx="90" cy="90" r="65" fill="none" stroke="#1e293b" stroke-width="32"/>
                <circle v-for="(seg, i) in donutSegments" :key="i" cx="90" cy="90" r="65" fill="none" :stroke="seg.color" stroke-width="32" :stroke-dasharray="`${seg.dash} ${408-seg.dash}`" :stroke-dashoffset="-seg.offset"/>
                <text x="90" y="86"  fill="#f1f5f9" font-size="14" font-weight="700" text-anchor="middle">{{ expenseByCategory.length }}</text>
                <text x="90" y="102" fill="#64748b" font-size="10" text-anchor="middle">kategori</text>
              </svg>
            </div>
            <div class="donut-legend">
              <div v-for="cat in expenseByCategory.slice(0,5)" :key="cat.name" class="legend-item">
                <span class="legend-dot-sm" :style="`background:${cat.color}`"></span>
                <span class="legend-name">{{ cat.name }}</span>
                <span class="legend-val">Rp {{ Number(cat.total).toLocaleString('id-ID') }}</span>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- ── SAVING SECTION ─────────────────────────────────────────── -->
      <div class="section-title-row">
        <div class="section-title-wrap">
          <span class="section-icon">🐷</span>
          <h2 class="section-title">Tabungan</h2>
        </div>
        <Link href="/savings" class="view-all-link">Kelola Tabungan →</Link>
      </div>

      <!-- Saving summary -->
      <div class="saving-stats-row">
        <div class="saving-stat">
          <div class="saving-stat-label">Total Tersimpan di Tabungan</div>
          <div class="saving-stat-value">Rp {{ stats.totalSavings }}</div>
        </div>
        <div class="saving-stat accent">
          <div class="saving-stat-label">Disisihkan Bulan Ini</div>
          <div class="saving-stat-value">Rp {{ stats.monthlySavings }}</div>
        </div>
      </div>

      <!-- Saving Charts Row -->
      <div class="saving-charts-row">

        <!-- Line Chart: akumulasi tabungan -->
        <div class="chart-card">
          <div class="chart-header">
            <h3 class="chart-title">Akumulasi Tabungan</h3>
            <div class="chart-legend">
              <span class="legend-dot" style="background:#8b5cf6"></span><span>Total tersimpan</span>
            </div>
          </div>
          <div v-if="isSavingChartEmpty" class="chart-empty">
            <span style="font-size:36px">🐷</span>
            <p>Belum ada aktivitas tabungan.</p>
          </div>
          <div v-else class="chart-wrap" ref="savingChartWrap" @mousemove="onSavingChartHover" @mouseleave="savingTooltip.visible = false">
            <svg class="line-svg" :viewBox="`0 0 ${lW} ${lH}`" preserveAspectRatio="none">
              <defs>
                <linearGradient id="savingGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.3"/>
                  <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0"/>
                </linearGradient>
              </defs>
              <line v-for="g in savingYGrid" :key="g" :x1="yPad" :y1="savingYScale(g)" :x2="lW" :y2="savingYScale(g)" stroke="#ffffff08" stroke-width="1"/>
              <path :d="savingAreaPath" fill="url(#savingGrad)"/>
              <polyline :points="savingLinePath" fill="none" stroke="#8b5cf6" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
              <line v-if="savingTooltip.visible" :x1="savingTooltip.svgX" y1="0" :x2="savingTooltip.svgX" :y2="lH-18" stroke="#ffffff15" stroke-width="1.5" stroke-dasharray="4 3"/>
              <circle v-for="(pt, i) in savingPoints" :key="i" :cx="pt.x" :cy="pt.y" r="4" fill="#8b5cf6" stroke="#080c14" stroke-width="2" :opacity="savingTooltip.visible && savingTooltip.index===i ? 0 : 1"/>
              <circle v-if="savingTooltip.visible && savingTooltip.index >= 0" :cx="savingPoints[savingTooltip.index].x" :cy="savingPoints[savingTooltip.index].y" r="6" fill="#8b5cf6" stroke="#080c14" stroke-width="2.5"/>
              <text v-for="(pt, i) in savingPoints" :key="'sl'+i" :x="pt.x" :y="lH-2" fill="#475569" font-size="11" text-anchor="middle">{{ savingChartData[i].month }}</text>
            </svg>
            <div v-if="savingTooltip.visible" class="chart-tooltip" :style="savingTooltipStyle">
              <div class="tooltip-month">{{ savingTooltip.month }}</div>
              <div class="tooltip-row">
                <span class="tooltip-dot" style="background:#8b5cf6"></span>
                <span class="tooltip-label">Total Tabungan</span>
                <span class="tooltip-val" style="color:#8b5cf6">Rp {{ savingTooltip.amount }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Progress bar tabungan aktif -->
        <div class="chart-card">
          <div class="chart-header">
            <h3 class="chart-title">Progress Tabungan Aktif</h3>
            <Link href="/savings" class="view-all-link" style="font-size:12px">Lihat semua</Link>
          </div>
          <div v-if="savingGoals.length === 0" class="chart-empty">
            <span style="font-size:36px">🎯</span>
            <p>Belum ada tabungan aktif.</p>
            <Link href="/savings" class="btn-start-saving">+ Buat Tabungan</Link>
          </div>
          <div v-else class="saving-progress-list">
            <div v-for="goal in savingGoals.slice(0,5)" :key="goal.id" class="saving-progress-item">
              <div class="saving-progress-top">
                <div class="saving-progress-name">
                  <span class="saving-emoji">{{ goal.icon }}</span>
                  <span>{{ goal.name }}</span>
                </div>
                <span class="saving-pct" :style="`color: ${goal.color}`">{{ goal.pct }}%</span>
              </div>
              <div class="saving-track">
                <div class="saving-bar" :style="`width: ${goal.pct}%; background: ${goal.color}`"></div>
              </div>
              <div class="saving-amounts">
                <span class="saving-current">Rp {{ goal.current }}</span>
                <span class="saving-target">/ Rp {{ goal.target }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div class="recent-card">
        <div class="recent-header">
          <h3 class="chart-title">Transaksi Terbaru</h3>
          <Link href="/transactions" class="view-all-link">Lihat Semua →</Link>
        </div>
        <div class="tx-list">
          <div v-for="(tx, i) in recentTransactions" :key="tx.id" class="tx-item" :style="`--delay: ${i * 0.07}s`">
            <div class="tx-icon" :class="tx.transfer ? 'transfer' : tx.income ? 'income' : 'expense'">
              <svg v-if="tx.income"     width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
              <svg v-else-if="tx.transfer" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 9l4-4 4 4M9 5v10M19 15l-4 4-4-4M15 19V9"/></svg>
              <svg v-else               width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
            </div>
            <div class="tx-info">
              <span class="tx-name">{{ tx.name }}</span>
              <span class="tx-meta">{{ tx.category }} · {{ tx.date }}</span>
            </div>
            <span :class="['tx-amount', tx.income ? 'pos' : tx.transfer ? 'trn' : 'neg']">
              {{ tx.income ? '+' : tx.transfer ? '⇄' : '-' }}Rp {{ tx.amount }}
            </span>
          </div>
          <div v-if="recentTransactions.length === 0" class="empty-tx">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#334155" stroke-width="1.5"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            <p>Belum ada transaksi.</p>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link }  from '@inertiajs/vue3'

export default {
  name: 'Dashboard',
  components: { AppLayout, Link },
  props: {
    stats:              { type: Object, default: () => ({}) },
    chartData:          { type: Array,  default: () => [] },
    savingChartData:    { type: Array,  default: () => [] },
    savingGoals:        { type: Array,  default: () => [] },
    expenseByCategory:  { type: Array,  default: () => [] },
    recentTransactions: { type: Array,  default: () => [] },
  },
  data() {
    return {
      lW: 500, lH: 200, yPad: 30,
      tooltip:       { visible: false, index: -1, svgX: 0, x: 0, y: 0, month: '', income: '', expense: '', diff: '', diffPositive: true },
      savingTooltip: { visible: false, index: -1, svgX: 0, x: 0, y: 0, month: '', amount: '' },
    }
  },
  computed: {
    today() {
      return new Date().toLocaleDateString('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric' })
    },
    statCards() {
      const s = this.stats
      return [
        { label:'Total Saldo',                    value: s.totalBalance,   change:'Saldo dompet aktif',                                                                          changeClass:'neutral',  iconBg:'#10b98120', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>' },
        { label:`Pemasukan — ${s.activeMonth}`,   value: s.monthlyIncome,  change: s.incomeChange  !== 0 ? `${s.incomeChange  >= 0?'+':''}${s.incomeChange}% dari bulan lalu`  : 'Bulan pertama', changeClass: s.incomeChange  >= 0 ? 'positive':'negative', iconBg:'#3b82f620', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>' },
        { label:`Pengeluaran — ${s.activeMonth}`, value: s.monthlyExpense, change: s.expenseChange !== 0 ? `${s.expenseChange >= 0?'+':''}${s.expenseChange}% dari bulan lalu` : 'Bulan pertama', changeClass: s.expenseChange <= 0 ? 'positive':'negative', iconBg:'#ef444420', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>' },
        { label:`Disisihkan — ${s.activeMonth}`,  value: s.monthlySavings, change:'Transfer ke tabungan bulan ini',                                                             changeClass:'neutral',  iconBg:'#8b5cf620', icon:'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><path d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2h0V5z"/></svg>' },
      ]
    },
    // Income/Expense chart
    isChartEmpty() { return !this.chartData?.length || this.chartData.every(d => d.income === 0 && d.expense === 0) },
    maxVal()   { return Math.max(...(this.chartData || []).flatMap(d => [d.income, d.expense]), 1) },
    yGrid()    { const s = Math.ceil(this.maxVal / 4 / 500) * 500 || 500; return [0,s,s*2,s*3,s*4].filter(v => v <= this.maxVal*1.2) },
    linePoints() {
      return (key) => {
        if (!this.chartData?.length) return []
        const n = this.chartData.length
        return this.chartData.map((d, i) => ({
          x: this.yPad + (n > 1 ? i/(n-1) : 0.5) * (this.lW - this.yPad - 10),
          y: this.yScale(d[key]),
        }))
      }
    },
    linePath() { return (key) => this.linePoints(key).map(p => `${p.x},${p.y}`).join(' ') },
    areaPath() {
      return (key) => {
        const pts = this.linePoints(key); if (!pts.length) return ''
        const b = this.lH - 18
        return `M${pts[0].x},${b} ` + pts.map(p => `L${p.x},${p.y}`).join(' ') + ` L${pts[pts.length-1].x},${b} Z`
      }
    },
    tooltipStyle() {
      const wrap = this.$refs.chartWrap; if (!wrap) return {}
      const w = wrap.clientWidth, h = wrap.clientHeight, ttW = 190, ttH = 120
      let x = this.tooltip.x + 16, y = this.tooltip.y - ttH/2
      if (x + ttW > w - 8) x = this.tooltip.x - ttW - 16
      if (y < 8) y = 8; if (y + ttH > h - 8) y = h - ttH - 8
      return { left: `${x}px`, top: `${y}px` }
    },
    // Saving chart
    isSavingChartEmpty() { return !this.savingChartData?.length || this.savingChartData.every(d => d.amount === 0) },
    savingMaxVal() { return Math.max(...(this.savingChartData || []).map(d => d.amount), 1) },
    savingYGrid()  { const s = Math.ceil(this.savingMaxVal / 4 / 500) * 500 || 500; return [0,s,s*2,s*3,s*4].filter(v => v <= this.savingMaxVal*1.2) },
    savingPoints() {
      if (!this.savingChartData?.length) return []
      const n = this.savingChartData.length
      return this.savingChartData.map((d, i) => ({
        x: this.yPad + (n > 1 ? i/(n-1) : 0.5) * (this.lW - this.yPad - 10),
        y: this.savingYScale(d.amount),
      }))
    },
    savingLinePath() { return this.savingPoints.map(p => `${p.x},${p.y}`).join(' ') },
    savingAreaPath() {
      const pts = this.savingPoints; if (!pts.length) return ''
      const b = this.lH - 18
      return `M${pts[0].x},${b} ` + pts.map(p => `L${p.x},${p.y}`).join(' ') + ` L${pts[pts.length-1].x},${b} Z`
    },
    savingTooltipStyle() {
      const wrap = this.$refs.savingChartWrap; if (!wrap) return {}
      const ttW = 190, ttH = 80
      let x = this.savingTooltip.x + 16, y = this.savingTooltip.y - ttH/2
      if (x + ttW > wrap.clientWidth - 8) x = this.savingTooltip.x - ttW - 16
      if (y < 8) y = 8
      return { left: `${x}px`, top: `${y}px` }
    },
    donutSegments() {
      const total = this.expenseByCategory.reduce((s, c) => s + Number(c.total), 0)
      const C = 408; let offset = 0
      return this.expenseByCategory.map(cat => {
        const dash = total > 0 ? (Number(cat.total)/total)*C : 0
        const seg = { color: cat.color, dash, offset }; offset += dash; return seg
      })
    },
  },
  methods: {
    yScale(val)       { const b = this.lH-18; return b - (val/(this.maxVal*1.2))*(b-16) },
    savingYScale(val) { const b = this.lH-18; return b - (val/(this.savingMaxVal*1.2))*(b-16) },
    onChartHover(event) {
      const wrap = this.$refs.chartWrap; if (!wrap || !this.chartData?.length) return
      const rect = wrap.getBoundingClientRect()
      const mouseX = event.clientX - rect.left, mouseY = event.clientY - rect.top
      const svgX = (mouseX / rect.width) * this.lW
      const pts = this.linePoints('income')
      let best = 0, bestDist = Infinity
      pts.forEach((p, i) => { const d = Math.abs(p.x - svgX); if (d < bestDist) { bestDist = d; best = i } })
      const d = this.chartData[best], diff = d.income - d.expense
      this.tooltip = { visible: true, index: best, svgX: pts[best].x, x: mouseX, y: mouseY,
        month: d.month, income: Number(d.income).toLocaleString('id-ID'),
        expense: Number(d.expense).toLocaleString('id-ID'),
        diff: Math.abs(diff).toLocaleString('id-ID'), diffPositive: diff >= 0 }
    },
    onSavingChartHover(event) {
      const wrap = this.$refs.savingChartWrap; if (!wrap || !this.savingChartData?.length) return
      const rect = wrap.getBoundingClientRect()
      const mouseX = event.clientX - rect.left, mouseY = event.clientY - rect.top
      const svgX = (mouseX / rect.width) * this.lW
      const pts = this.savingPoints
      let best = 0, bestDist = Infinity
      pts.forEach((p, i) => { const d = Math.abs(p.x - svgX); if (d < bestDist) { bestDist = d; best = i } })
      const d = this.savingChartData[best]
      this.savingTooltip = { visible: true, index: best, svgX: pts[best].x, x: mouseX, y: mouseY,
        month: d.month, amount: Number(d.amount).toLocaleString('id-ID') }
    },
  }
}
</script>

<style scoped>
.page-content { padding: 32px; display: flex; flex-direction: column; gap: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; animation: fadeUp 0.35s ease both; }
.page-title { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 800; color: var(--text-heading); letter-spacing: -0.01em; }
.page-subtitle { font-size: 14px; color: var(--text-muted); margin-top: 4px; }
.header-date { font-size: 13px; color: var(--text-muted); background: var(--card); border: 1px solid var(--border); border-radius: 8px; padding: 8px 14px; }
.active-month-banner { display: flex; align-items: center; gap: 8px; background: #3b82f610; border: 1px solid #3b82f620; border-radius: 10px; padding: 10px 16px; font-size: 13px; color: #64748b; }
.active-month-banner strong { color: #93c5fd; }
.stat-cards { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; animation: fadeUp 0.35s ease 0.06s both; }
.stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 18px; padding: 22px; animation: fadeUp 0.4s ease both; animation-delay: var(--delay); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;}
.stat-card:hover { border-color: #3b82f640; transform: translateY(-6px); box-shadow: 0 12px 24px -10px rgba(0,0,0,0.3);}
.stat-card:hover .card-icon { transform: scale(1.1) rotate(5deg); transition: transform 0.3s ease; }
.card-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.card-label { font-size: 12px; font-weight: 500; color: var(--text-muted); line-height: 1.4; }
.card-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.card-value { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; color: var(--text-heading); letter-spacing: -0.02em; margin-bottom: 8px; }
.card-change { font-size: 12px; font-weight: 500; }
.card-change.positive { color: #10b981; } .card-change.negative { color: #ef4444; } .card-change.neutral { color: #64748b; }
.charts-row { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; animation: fadeUp 0.35s ease 0.1s both; }
.chart-card { background: var(--card); border: 1px solid var(--border); border-radius: 18px; padding: 24px; }
.chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.chart-title { font-size: 15px; font-weight: 600; color: var(--text-body); }
.chart-legend { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-muted); }
.legend-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.chart-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; padding: 32px 0; text-align: center; }
.chart-empty p { font-size: 14px; font-weight: 500; color: #475569; }
.chart-wrap { width: 100%; position: relative; cursor: crosshair; }
.line-svg { width: 100%; height: 200px; display: block; overflow: visible; }
.chart-tooltip { position: absolute; pointer-events: none; background: var(--card2,#131b2e); border: 1px solid #ffffff15; border-radius: 12px; padding: 12px 14px; width: 190px; box-shadow: 0 8px 32px rgba(0,0,0,0.4); z-index: 20; animation: tooltipIn 0.12s ease; }
.tooltip-month { font-size: 13px; font-weight: 700; color: var(--text-heading,#f1f5f9); margin-bottom: 10px; }
.tooltip-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 13px; }
.tooltip-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.tooltip-label { flex: 1; color: var(--text-muted,#64748b); }
.tooltip-val { font-weight: 600; font-variant-numeric: tabular-nums; }
.tooltip-val.income { color: #10b981; } .tooltip-val.expense { color: #ef4444; }
.tooltip-divider { height: 1px; background: #ffffff0a; margin: 8px 0 6px; }
.donut-wrap { display: flex; justify-content: center; margin-bottom: 16px; }
.donut-svg { width: 160px; height: 160px; }
.donut-legend { display: flex; flex-direction: column; gap: 8px; }
.legend-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-sub,#94a3b8); }
.legend-dot-sm { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.legend-name { flex: 1; } .legend-val { font-weight: 600; color: var(--text-body); font-variant-numeric: tabular-nums; }
/* Saving section */
.section-title-row { display: flex; justify-content: space-between; align-items: center; animation: fadeUp 0.35s ease 0.14s both; }
.section-title-wrap { display: flex; align-items: center; gap: 10px; }
.section-icon { font-size: 20px; }
.section-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--text-heading); }
.view-all-link { font-size: 13px; color: #10b981; font-weight: 500; text-decoration: none; transition: opacity 0.2s; }
.view-all-link:hover { opacity: 0.7; }
.saving-stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; animation: fadeUp 0.35s ease 0.16s both; }
.saving-stat { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 18px 22px; }
.saving-stat.accent { border-color: #8b5cf630; background: #8b5cf608; }
.saving-stat-label { font-size: 12px; color: #64748b; margin-bottom: 6px; }
.saving-stat-value { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; color: var(--text-heading); }
.saving-stat.accent .saving-stat-value { color: #8b5cf6; }
.saving-charts-row { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; animation: fadeUp 0.35s ease 0.18s both; }
.saving-progress-list { display: flex; flex-direction: column; gap: 16px; }
.saving-progress-item { display: flex; flex-direction: column; gap: 6px; }
.saving-progress-top { display: flex; justify-content: space-between; align-items: center; }
.saving-progress-name { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: var(--text-body); }
.saving-emoji { font-size: 16px; }
.saving-pct { font-size: 13px; font-weight: 700; }
.saving-track { height: 7px; background: var(--card2); border-radius: 99px; overflow: hidden; }
.saving-bar { height: 100%; border-radius: 99px; transition: width 0.6s ease; }
.saving-amounts { display: flex; align-items: baseline; gap: 4px; }
.saving-current { font-size: 13px; font-weight: 600; color: var(--text-body); }
.saving-target  { font-size: 12px; color: #64748b; }
.btn-start-saving { margin-top: 4px; padding: 8px 20px; background: #8b5cf620; color: #8b5cf6; border: 1px solid #8b5cf630; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; font-family: 'DM Sans', sans-serif; }
/* Recent */
.recent-card { background: var(--card); border: 1px solid var(--border); border-radius: 18px; padding: 24px; animation: fadeUp 0.35s ease 0.22s both; }
.recent-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.tx-list { display: flex; flex-direction: column; gap: 4px; }
.tx-item { display: flex; align-items: center; gap: 14px; padding: 12px; border-radius: 12px; animation: fadeUp 0.3s ease both; animation-delay: var(--delay); transition: all 0.2s ease; }
.tx-item:hover { background: var(--border2,#ffffff05); padding-left: 18px; cursor: pointer; }
.tx-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.tx-icon.income   { background: #10b98120; color: #10b981; }
.tx-icon.expense  { background: #ef444420; color: #ef4444; }
.tx-icon.transfer { background: #8b5cf620; color: #8b5cf6; }
.tx-info { flex: 1; display: flex; flex-direction: column; gap: 3px; }
.tx-name { font-size: 14px; font-weight: 500; color: var(--text-body); }
.tx-meta { font-size: 12px; color: var(--text-muted); }
.tx-amount { font-size: 14px; font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
.tx-amount.pos { color: #10b981; } .tx-amount.neg { color: #ef4444; } .tx-amount.trn { color: #8b5cf6; }
.empty-tx { display: flex; flex-direction: column; align-items: center; gap: 10px; text-align: center; padding: 32px 0; }
.empty-tx p { font-size: 14px; color: #475569; }
@keyframes fadeUp    { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
@keyframes tooltipIn { from { opacity: 0; transform: scale(0.95); }      to { opacity: 1; transform: scale(1); } }

/* ── Responsive Dashboard ─────────────────────────── */
@media (max-width: 1024px) {
  .stat-cards        { grid-template-columns: repeat(2, 1fr); }
  .charts-row        { grid-template-columns: 1fr; }
  .saving-charts-row { grid-template-columns: 1fr; }
  .saving-stats-row  { grid-template-columns: 1fr 1fr; }
  .page-content      { padding: 20px; }
}
@media (max-width: 768px) {
  .stat-cards        { grid-template-columns: 1fr 1fr; gap: 10px; }
  .page-content      { padding: 16px; gap: 16px; }
  .page-title        { font-size: 20px; }
  .header-date       { display: none; }
  .card-value        { font-size: 17px; }
  .saving-stats-row  { grid-template-columns: 1fr; }
  .section-title-row { flex-wrap: wrap; gap: 8px; }
  .chart-card        { padding: 16px; }
  .recent-card       { padding: 16px; }
  .tx-meta           { font-size: 11px; }
  .active-month-banner { font-size: 12px; padding: 8px 12px; }
}
@media (max-width: 480px) {
  .stat-cards { grid-template-columns: 1fr; }
}

</style>