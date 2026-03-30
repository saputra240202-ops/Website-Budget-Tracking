<template>
  <AppLayout>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Financial Reports</h1>
          <p class="page-subtitle">Detailed analytics of your income and expenses.</p>
        </div>
        <select v-model="period" @change="changePeriod" class="period-select">
          <option value="3">3 Bulan</option>
          <option value="6">6 Bulan</option>
          <option value="12">12 Bulan</option>
        </select>
      </div>

      <!-- Stat Cards income/expense -->
      <div class="stat-cards">
        <div class="stat-card" style="--delay:0s">
          <div class="stat-card-top">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            <span class="stat-label">Total Pemasukan</span>
          </div>
          <div class="stat-value">Rp {{ stats.totalIncome }}</div>
          <div class="stat-change" :class="stats.incomeChange >= 0 ? 'positive' : 'negative'">
            {{ stats.incomeChange >= 0 ? '+' : '' }}{{ stats.incomeChange }}% vs periode sebelumnya
          </div>
        </div>
        <div class="stat-card" style="--delay:0.08s">
          <div class="stat-card-top">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
            <span class="stat-label">Total Pengeluaran</span>
          </div>
          <div class="stat-value">Rp {{ stats.totalExpense }}</div>
          <div class="stat-change" :class="stats.expenseChange <= 0 ? 'positive' : 'negative'">
            {{ stats.expenseChange >= 0 ? '+' : '' }}{{ stats.expenseChange }}% vs periode sebelumnya
          </div>
        </div>
        <div class="stat-card" style="--delay:0.16s">
          <div class="stat-card-top">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span class="stat-label">Net Balance</span>
          </div>
          <div class="stat-value">Rp {{ stats.netBalance }}</div>
          <div class="stat-change neutral">Savings rate {{ stats.savingsRate }}%</div>
        </div>
      </div>

      <!-- Charts Row: bar + donut -->
      <div class="charts-row">
        <div class="chart-card bar-card">
          <div class="chart-title-row">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <h3 class="chart-title">Monthly Expenses</h3>
          </div>
          <div v-if="isBarEmpty" class="chart-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#334155" stroke-width="1.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <p>Belum ada data pengeluaran.</p>
          </div>
          <div v-else class="bar-wrap" ref="barWrap" @mousemove="onBarHover" @mouseleave="barTooltip.visible = false">
            <svg class="bar-chart-svg" :viewBox="`0 0 ${barW} ${barH}`">
              <line v-for="g in yGridLines" :key="g.y" :x1="yLabelW" :y1="g.sy" :x2="barW" :y2="g.sy" stroke="#ffffff08" stroke-width="1"/>
              <text v-for="g in yGridLines" :key="'l'+g.y" :x="yLabelW-8" :y="g.sy+4" fill="#475569" font-size="11" text-anchor="end">{{ g.label }}</text>
              <g v-for="(bar, i) in barData" :key="i">
                <rect :x="yLabelW + i*barStep" y="0" :width="barStep" :height="barH-bottomPad" fill="transparent"/>
                <rect :x="yLabelW + i*barStep + barPad" :y="barH-bottomPad-bar.h" :width="barWidth" :height="bar.h" rx="6" :fill="barTooltip.visible && barTooltip.index===i ? '#34d399' : '#10b981'" :opacity="barTooltip.visible && barTooltip.index!==i ? 0.4 : 0.85" style="transition:opacity .15s,fill .15s;"/>
                <text :x="yLabelW + i*barStep + barPad + barWidth/2" :y="barH-bottomPad+18" :fill="barTooltip.visible && barTooltip.index===i ? '#94a3b8' : '#475569'" :font-weight="barTooltip.visible && barTooltip.index===i ? '700':'400'" font-size="11" text-anchor="middle">{{ bar.month }}</text>
              </g>
            </svg>
            <div v-if="barTooltip.visible" class="bar-tooltip" :style="barTooltipStyle">
              <div class="tooltip-month">{{ barTooltip.monthFull }}</div>
              <div class="tooltip-row"><span class="tooltip-dot" style="background:#10b981"></span><span class="tooltip-label">Pengeluaran</span><span class="tooltip-val">Rp {{ barTooltip.value }}</span></div>
            </div>
          </div>
        </div>

        <div class="chart-card donut-card">
          <div class="chart-title-row">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
            <h3 class="chart-title">Spending by Category</h3>
          </div>
          <div class="donut-wrap">
            <svg viewBox="0 0 180 180" class="donut-svg">
              <circle cx="90" cy="90" r="65" fill="none" stroke="#1e293b" stroke-width="32"/>
              <circle v-for="(seg, i) in donutSegments" :key="i" cx="90" cy="90" r="65" fill="none" :stroke="seg.color" stroke-width="32" :stroke-dasharray="`${seg.dash} ${408-seg.dash}`" :stroke-dashoffset="-seg.offset"/>
            </svg>
          </div>
          <div class="donut-legend">
            <div v-for="cat in spendingByCategory.slice(0,5)" :key="cat.name" class="legend-item">
              <span class="legend-dot" :style="`background:${cat.color}`"></span>
              <span class="legend-name">{{ cat.name }}</span>
              <span class="legend-pct">{{ cat.pct }}%</span>
              <span class="legend-val">Rp {{ Number(cat.total).toLocaleString('id-ID') }}</span>
            </div>
            <div v-if="spendingByCategory.length === 0" class="empty-legend">Belum ada data pengeluaran.</div>
          </div>
        </div>
      </div>

      <!-- ── SECTION TABUNGAN ────────────────────────────────────────────── -->
      <div class="saving-section">
        <div class="saving-section-header">
          <div class="saving-section-title-wrap">
            <span style="font-size:18px">🐷</span>
            <h2 class="saving-section-title">Aktivitas Tabungan</h2>
            <span class="section-period-badge">{{ selectedPeriod }} Bulan Terakhir</span>
          </div>
        </div>

        <!-- Saving stat cards -->
        <div class="saving-stat-cards">
          <div class="saving-stat-card">
            <div class="saving-stat-icon" style="background:#8b5cf618;border-color:#8b5cf630">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <div>
              <div class="saving-stat-label">Total Disisihkan</div>
              <div class="saving-stat-value">Rp {{ savingStats.totalDeposited }}</div>
            </div>
          </div>
          <div class="saving-stat-card">
            <div class="saving-stat-icon" style="background:#ef444418;border-color:#ef444430">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <div>
              <div class="saving-stat-label">Total Ditarik</div>
              <div class="saving-stat-value">Rp {{ savingStats.totalWithdrawn }}</div>
            </div>
          </div>
          <div class="saving-stat-card">
            <div class="saving-stat-icon" style="background:#10b98118;border-color:#10b98130">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
              <div class="saving-stat-label">Net Tersimpan</div>
              <div class="saving-stat-value" style="color:#10b981">Rp {{ savingStats.netSaved }}</div>
            </div>
          </div>
        </div>

        <!-- Saving bar chart per bulan -->
        <div class="saving-charts-row">
          <div class="chart-card">
            <div class="chart-title-row">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              <h3 class="chart-title">Setoran per Bulan</h3>
            </div>
            <div v-if="isSavingBarEmpty" class="chart-empty">
              <span style="font-size:32px">🐷</span>
              <p>Belum ada aktivitas tabungan.</p>
            </div>
            <div v-else class="bar-wrap" ref="savingBarWrap" @mousemove="onSavingBarHover" @mouseleave="savingBarTooltip.visible = false">
              <svg class="bar-chart-svg" :viewBox="`0 0 ${barW} ${barH}`">
                <line v-for="g in savingYGridLines" :key="g.y" :x1="yLabelW" :y1="g.sy" :x2="barW" :y2="g.sy" stroke="#ffffff08" stroke-width="1"/>
                <text v-for="g in savingYGridLines" :key="'sl'+g.y" :x="yLabelW-8" :y="g.sy+4" fill="#475569" font-size="11" text-anchor="end">{{ g.label }}</text>
                <g v-for="(bar, i) in savingBarData" :key="i">
                  <rect :x="yLabelW + i*barStep" y="0" :width="barStep" :height="barH-bottomPad" fill="transparent"/>
                  <rect :x="yLabelW + i*barStep + barPad" :y="barH-bottomPad-bar.h" :width="barWidth" :height="bar.h" rx="6" :fill="savingBarTooltip.visible && savingBarTooltip.index===i ? '#a78bfa' : '#8b5cf6'" :opacity="savingBarTooltip.visible && savingBarTooltip.index!==i ? 0.4 : 0.85" style="transition:opacity .15s,fill .15s;"/>
                  <text :x="yLabelW + i*barStep + barPad + barWidth/2" :y="barH-bottomPad+18" fill="#475569" font-size="11" text-anchor="middle">{{ bar.month }}</text>
                </g>
              </svg>
              <div v-if="savingBarTooltip.visible" class="bar-tooltip" :style="savingBarTooltipStyle">
                <div class="tooltip-month">{{ savingBarTooltip.monthFull }}</div>
                <div class="tooltip-row"><span class="tooltip-dot" style="background:#8b5cf6"></span><span class="tooltip-label">Disisihkan</span><span class="tooltip-val" style="color:#8b5cf6">Rp {{ savingBarTooltip.deposited }}</span></div>
                <div class="tooltip-row" v-if="savingBarTooltip.withdrawn > 0"><span class="tooltip-dot" style="background:#ef4444"></span><span class="tooltip-label">Ditarik</span><span class="tooltip-val" style="color:#ef4444">Rp {{ savingBarTooltip.withdrawn }}</span></div>
              </div>
            </div>
          </div>

          <!-- Progress semua tabungan -->
          <div class="chart-card">
            <div class="chart-title-row">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <h3 class="chart-title">Progress Tabungan</h3>
            </div>
            <div v-if="savingProgress.length === 0" class="chart-empty">
              <span style="font-size:32px">🎯</span>
              <p>Belum ada tabungan.</p>
            </div>
            <div v-else class="saving-progress-list">
              <div v-for="goal in savingProgress" :key="goal.id" class="saving-progress-item">
                <div class="saving-progress-top">
                  <div class="saving-progress-name">
                    <span>{{ goal.icon }}</span>
                    <span>{{ goal.name }}</span>
                    <span :class="['status-pill', goal.status]">{{ goal.status === 'completed' ? '✓' : '' }}</span>
                  </div>
                  <span class="saving-pct" :style="`color:${goal.color}`">{{ goal.pct }}%</span>
                </div>
                <div class="saving-track">
                  <div class="saving-bar" :style="`width:${goal.pct}%;background:${goal.color}`"></div>
                </div>
                <div class="saving-amounts">
                  <span class="saving-current">Rp {{ goal.current }}</span>
                  <span class="saving-target">/ Rp {{ goal.target }}</span>
                </div>
              </div>
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
  name: 'Reports',
  components: { AppLayout },
  props: {
    stats:               { type: Object, default: () => ({}) },
    monthlyExpenses:     { type: Array,  default: () => [] },
    spendingByCategory:  { type: Array,  default: () => [] },
    selectedPeriod:      { type: Number, default: 6 },
    savingStats:         { type: Object, default: () => ({}) },
    monthlySavings:      { type: Array,  default: () => [] },
    savingProgress:      { type: Array,  default: () => [] },
  },
  data() {
    return {
      period: this.selectedPeriod,
      barW: 520, barH: 260, yLabelW: 50, bottomPad: 30, barPad: 8,
      barTooltip:       { visible: false, index: -1, x: 0, y: 0, monthFull: '', value: '' },
      savingBarTooltip: { visible: false, index: -1, x: 0, y: 0, monthFull: '', deposited: '', withdrawn: 0 },
    }
  },
  computed: {
    // ── Expense bar chart ─────────────────────────────────────────
    isBarEmpty()  { return !this.monthlyExpenses.length || this.monthlyExpenses.every(d => d.value === 0) },
    barStep()     { return this.monthlyExpenses.length ? (this.barW - this.yLabelW) / this.monthlyExpenses.length : 1 },
    barWidth()    { return Math.max(this.barStep - this.barPad*2, 4) },
    maxVal()      { return Math.max(...this.monthlyExpenses.map(d => d.value), 1) },
    chartHeight() { return this.barH - this.bottomPad },
    barData() {
      return this.monthlyExpenses.map(d => ({
        month: d.month, monthFull: d.month_full, value: d.value,
        h: Math.max((d.value / (this.maxVal*1.1)) * (this.chartHeight-10), d.value > 0 ? 4 : 0)
      }))
    },
    yGridLines() {
      const steps = 4, step = Math.ceil(this.maxVal/steps/500)*500 || 500
      return Array.from({ length: steps+1 }, (_, i) => {
        const v = i*step
        return { y: v, label: v>=1000000 ? `${v/1000000}jt` : v>=1000 ? `${v/1000}rb` : v,
          sy: this.chartHeight - (v/(this.maxVal*1.1))*(this.chartHeight-10) }
      })
    },
    barTooltipStyle() {
      const wrap = this.$refs.barWrap; if (!wrap) return {}
      const ttW = 200
      let x = this.barTooltip.x + 16, y = this.barTooltip.y - 50
      if (x + ttW > wrap.clientWidth - 8) x = this.barTooltip.x - ttW - 16
      if (y < 8) y = 8
      return { left: `${x}px`, top: `${y}px` }
    },

    // ── Saving bar chart ──────────────────────────────────────────
    isSavingBarEmpty() { return !this.monthlySavings.length || this.monthlySavings.every(d => d.deposited === 0) },
    savingMaxVal()     { return Math.max(...this.monthlySavings.map(d => d.deposited), 1) },
    savingBarData() {
      return this.monthlySavings.map(d => ({
        month: d.month, monthFull: d.month_full, deposited: d.deposited, withdrawn: d.withdrawn,
        h: Math.max((d.deposited/(this.savingMaxVal*1.1))*(this.chartHeight-10), d.deposited > 0 ? 4 : 0)
      }))
    },
    savingYGridLines() {
      const steps = 4, step = Math.ceil(this.savingMaxVal/steps/500)*500 || 500
      return Array.from({ length: steps+1 }, (_, i) => {
        const v = i*step
        return { y: v, label: v>=1000000 ? `${v/1000000}jt` : v>=1000 ? `${v/1000}rb` : v,
          sy: this.chartHeight - (v/(this.savingMaxVal*1.1))*(this.chartHeight-10) }
      })
    },
    savingBarTooltipStyle() {
      const wrap = this.$refs.savingBarWrap; if (!wrap) return {}
      const ttW = 210
      let x = this.savingBarTooltip.x + 16, y = this.savingBarTooltip.y - 50
      if (x + ttW > wrap.clientWidth - 8) x = this.savingBarTooltip.x - ttW - 16
      if (y < 8) y = 8
      return { left: `${x}px`, top: `${y}px` }
    },

    donutSegments() {
      const total = this.spendingByCategory.reduce((s, c) => s + Number(c.total), 0)
      const C = 408; let offset = 0
      return this.spendingByCategory.map(cat => {
        const dash = total > 0 ? (Number(cat.total)/total)*C : 0
        const seg = { color: cat.color, dash, offset }; offset += dash; return seg
      })
    },
  },
  methods: {
    changePeriod() {
      router.get('/reports', { period: this.period }, { preserveState: true, replace: true })
    },
    onBarHover(event) {
      const wrap = this.$refs.barWrap; if (!wrap || !this.barData.length) return
      const rect = wrap.getBoundingClientRect()
      const mouseX = event.clientX - rect.left, mouseY = event.clientY - rect.top
      const svgX = (mouseX / rect.width) * this.barW
      const n = this.barData.length
      let hoveredIndex = -1
      for (let i = 0; i < n; i++) {
        const barLeft = this.yLabelW + i*this.barStep + this.barPad
        if (svgX >= barLeft - this.barPad && svgX <= barLeft + this.barWidth + this.barPad) { hoveredIndex = i; break }
      }
      if (hoveredIndex === -1) {
        let best = 0, bestDist = Infinity
        for (let i = 0; i < n; i++) {
          const cx = this.yLabelW + i*this.barStep + this.barPad + this.barWidth/2
          const dist = Math.abs(cx - svgX); if (dist < bestDist) { bestDist = dist; best = i }
        }
        hoveredIndex = best
      }
      const d = this.barData[hoveredIndex]
      this.barTooltip = { visible: true, index: hoveredIndex, x: mouseX, y: mouseY,
        monthFull: d.monthFull ?? d.month, value: Number(d.value).toLocaleString('id-ID') }
    },
    onSavingBarHover(event) {
      const wrap = this.$refs.savingBarWrap; if (!wrap || !this.savingBarData.length) return
      const rect = wrap.getBoundingClientRect()
      const mouseX = event.clientX - rect.left, mouseY = event.clientY - rect.top
      const svgX = (mouseX / rect.width) * this.barW
      const n = this.savingBarData.length
      let best = 0, bestDist = Infinity
      for (let i = 0; i < n; i++) {
        const cx = this.yLabelW + i*this.barStep + this.barPad + this.barWidth/2
        const dist = Math.abs(cx - svgX); if (dist < bestDist) { bestDist = dist; best = i }
      }
      const d = this.savingBarData[best]
      this.savingBarTooltip = { visible: true, index: best, x: mouseX, y: mouseY,
        monthFull: d.monthFull ?? d.month,
        deposited: Number(d.deposited).toLocaleString('id-ID'),
        withdrawn: d.withdrawn }
    },
  }
}
</script>

<style scoped>
.page-content { padding: 32px; display: flex; flex-direction: column; gap: 24px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; animation: fadeUp 0.35s ease both; }
.page-title { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 800; color: var(--text-heading); letter-spacing: -0.01em; }
.page-subtitle { font-size: 14px; color: #64748b; margin-top: 4px; }
.period-select { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 10px 36px 10px 14px; font-size: 13px; color: var(--text-sub); font-family: 'DM Sans', sans-serif; cursor: pointer; outline: none; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; background-color: var(--card); }
.stat-cards { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; animation: fadeUp 0.35s ease 0.06s both; }
.stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 22px 24px; animation: fadeUp 0.4s ease both; animation-delay: var(--delay); transition: border-color 0.2s, transform 0.2s; }
.stat-card:hover { border-color: #10b98130; transform: translateY(-2px); }
.stat-card-top { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
.stat-label { font-size: 13px; font-weight: 500; color: #64748b; }
.stat-value { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 800; color: var(--text-heading); letter-spacing: -0.02em; margin-bottom: 8px; }
.stat-change { font-size: 13px; font-weight: 500; }
.stat-change.positive { color: #10b981; } .stat-change.negative { color: #ef4444; } .stat-change.neutral { color: #64748b; }
.charts-row { display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; animation: fadeUp 0.35s ease 0.14s both; }
.chart-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; }
.chart-title-row { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.chart-title { font-size: 15px; font-weight: 600; color: var(--text-body); }
.bar-wrap { position: relative; cursor: crosshair; }
.bar-chart-svg { width: 100%; height: 260px; display: block; }
.bar-tooltip { position: absolute; pointer-events: none; background: var(--card2,#131b2e); border: 1px solid #ffffff15; border-radius: 12px; padding: 12px 14px; min-width: 180px; box-shadow: 0 8px 32px rgba(0,0,0,0.4); z-index: 20; animation: tooltipIn 0.12s ease; }
.tooltip-month { font-size: 13px; font-weight: 700; color: var(--text-heading,#f1f5f9); margin-bottom: 8px; }
.tooltip-row { display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 4px; }
.tooltip-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.tooltip-label { flex: 1; color: #64748b; }
.tooltip-val { font-weight: 600; color: #10b981; font-variant-numeric: tabular-nums; }
.chart-empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 48px 0; }
.chart-empty p { font-size: 14px; color: #475569; }
.donut-wrap { display: flex; justify-content: center; margin: 8px 0 16px; }
.donut-svg { width: 180px; height: 180px; }
.donut-legend { display: flex; flex-direction: column; gap: 9px; }
.legend-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-sub,#94a3b8); }
.legend-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.legend-name { flex: 1; }
.legend-pct { font-weight: 600; color: var(--text-body); min-width: 32px; text-align: right; }
.legend-val { font-size: 12px; color: #64748b; min-width: 90px; text-align: right; }
.empty-legend { font-size: 13px; color: #475569; text-align: center; padding: 12px 0; }
/* Saving section */
.saving-section { display: flex; flex-direction: column; gap: 20px; animation: fadeUp 0.35s ease 0.2s both; }
.saving-section-header { display: flex; justify-content: space-between; align-items: center; }
.saving-section-title-wrap { display: flex; align-items: center; gap: 10px; }
.saving-section-title { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--text-heading); }
.section-period-badge { font-size: 12px; background: #8b5cf618; color: #8b5cf6; border: 1px solid #8b5cf630; border-radius: 20px; padding: 3px 10px; font-weight: 500; }
.saving-stat-cards { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; }
.saving-stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 14px; transition: border-color 0.2s, transform 0.2s; }
.saving-stat-card:hover { transform: translateY(-2px); border-color: #8b5cf630; }
.saving-stat-icon { width: 40px; height: 40px; border-radius: 10px; border: 1px solid; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.saving-stat-label { font-size: 12px; color: #64748b; margin-bottom: 4px; }
.saving-stat-value { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: var(--text-heading); }
.saving-charts-row { display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; }
.saving-progress-list { display: flex; flex-direction: column; gap: 16px; }
.saving-progress-item { display: flex; flex-direction: column; gap: 6px; }
.saving-progress-top { display: flex; justify-content: space-between; align-items: center; }
.saving-progress-name { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: var(--text-body); }
.saving-pct { font-size: 13px; font-weight: 700; }
.saving-track { height: 7px; background: var(--card2); border-radius: 99px; overflow: hidden; }
.saving-bar { height: 100%; border-radius: 99px; transition: width 0.6s ease; }
.saving-amounts { display: flex; align-items: baseline; gap: 4px; }
.saving-current { font-size: 13px; font-weight: 600; color: var(--text-body); }
.saving-target  { font-size: 12px; color: #64748b; }
.status-pill { font-size: 11px; font-weight: 600; padding: 1px 7px; border-radius: 20px; }
.status-pill.completed { background: #3b82f618; color: #3b82f6; border: 1px solid #3b82f630; }
.status-pill.active    { display: none; }
@keyframes fadeUp    { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
@keyframes tooltipIn { from { opacity: 0; transform: scale(0.95); }      to { opacity: 1; transform: scale(1); } }

/* ── Responsive Reports ───────────────────────────── */
@media (max-width: 1024px) {
  .page-content        { padding: 20px; }
  .charts-row          { grid-template-columns: 1fr; }
  .saving-charts-row   { grid-template-columns: 1fr; }
  .stat-cards          { grid-template-columns: 1fr 1fr 1fr; }
  .saving-stat-cards   { grid-template-columns: 1fr 1fr 1fr; }
}
@media (max-width: 768px) {
  .page-content        { padding: 16px; gap: 16px; }
  .page-title          { font-size: 20px; }
  .page-header         { flex-wrap: wrap; gap: 10px; }
  .stat-cards          { grid-template-columns: 1fr; }
  .saving-stat-cards   { grid-template-columns: 1fr; }
  .stat-value          { font-size: 22px; }
  .chart-card          { padding: 16px; }
  .saving-section-title-wrap { flex-wrap: wrap; gap: 6px; }
}

</style>