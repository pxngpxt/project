<template>
  <div class="dashboard">
    <div class="page-header">
      <h1>🏠 Dashboard</h1>
      <p>ยินดีต้อนรับ, {{ user.name }} | {{ roleLabel }}</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card blue">
        <div class="stat-icon">🏢</div>
        <div class="stat-info">
          <span class="stat-num">{{ stats.total_rooms || 0 }}</span>
          <span class="stat-label">ห้องทั้งหมด</span>
        </div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <span class="stat-num">{{ stats.available_rooms || 0 }}</span>
          <span class="stat-label">ห้องว่างพร้อมใช้</span>
        </div>
      </div>
      <div class="stat-card yellow">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
          <span class="stat-num">{{ stats.pending_bookings || 0 }}</span>
          <span class="stat-label">รอการอนุมัติ</span>
        </div>
      </div>
      <div class="stat-card purple">
        <div class="stat-icon">📅</div>
        <div class="stat-info">
          <span class="stat-num">{{ stats.today_bookings || 0 }}</span>
          <span class="stat-label">จองวันนี้</span>
        </div>
      </div>
      <div class="stat-card teal" v-if="user.role_id <= 2">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
          <span class="stat-num">{{ stats.total_users || 0 }}</span>
          <span class="stat-label">ผู้ใช้ในระบบ</span>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="section-title">⚡ เมนูด่วน</div>
    <div class="quick-actions">
      <router-link to="/booking" class="quick-btn blue">
        <span>📅</span> จองห้องเลย
      </router-link>
      <router-link to="/rooms" class="quick-btn green">
        <span>🏢</span> ดูห้องทั้งหมด
      </router-link>
      <router-link to="/my-bookings" class="quick-btn yellow">
        <span>📋</span> การจองของฉัน
      </router-link>
      <router-link v-if="user.role_id <= 2" to="/manage-bookings" class="quick-btn purple">
        <span>✅</span> อนุมัติการจอง
      </router-link>
    </div>

    <!-- Recent Bookings -->
    <div class="section-title">🕐 การจองล่าสุด</div>
    <div class="card">
      <div v-if="loading" class="loading-box">กำลังโหลด...</div>
      <div v-else-if="recentBookings.length === 0" class="empty-box">ยังไม่มีการจอง</div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ผู้จอง</th>
              <th>ห้อง</th>
              <th>วัตถุประสงค์</th>
              <th>เวลาเริ่ม</th>
              <th>สถานะ</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in recentBookings" :key="b.booking_id">
              <td>{{ b.user_name }}</td>
              <td><strong>{{ b.room_name }}</strong></td>
              <td>{{ b.purpose }}</td>
              <td>{{ formatDate(b.start_time) }}</td>
              <td><span :class="statusBadge(b.status)">{{ statusLabel(b.status) }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      stats: {},
      recentBookings: [],
      loading: true,
      user: JSON.parse(localStorage.getItem('user') || '{}')
    }
  },

  computed: {
    roleLabel() {
      const map = { 1: '🔑 ผู้ดูแลระบบ', 2: '👤 เจ้าหน้าที่', 3: '🎓 นักศึกษา', 4: '👨‍🏫 อาจารย์' }
      return map[this.user.role_id] || 'ผู้ใช้'
    }
  },

  mounted() { this.loadDashboard() },

  methods: {
    async loadDashboard() {
      try {
        const res = await axios.get('http://localhost/project/api_php/Dashboard.php')
        if (res.data.success) {
          this.stats = res.data.data
          this.recentBookings = res.data.data.recent_bookings
        }
      } catch { /* silent */ }
      finally { this.loading = false }
    },

    formatDate(dt) {
      return new Date(dt).toLocaleString('th-TH', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
      })
    },

    statusLabel(s) {
      return { Pending: 'รอดำเนินการ', Approved: 'อนุมัติแล้ว', Rejected: 'ปฏิเสธ', Cancelled: 'ยกเลิก' }[s] || s
    },

    statusBadge(s) {
  return {
    Pending:   'status-tag pending',
    Approved:  'status-tag approved',
    Rejected:  'status-tag rejected',
    Cancelled: 'status-tag cancelled'
  }[s] || 'status-tag'
}
  }
}
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 16px; margin-bottom: 28px;
}

.stat-card {
  background: white; border-radius: 14px; padding: 20px;
  display: flex; align-items: center; gap: 14px;
  box-shadow: 0 2px 8px rgba(0,0,0,.06); border: 1px solid #e2e8f0;
  transition: .2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.1); }

.stat-icon { font-size: 28px; }
.stat-info { display: flex; flex-direction: column; }
.stat-num  { font-size: 26px; font-weight: 800; line-height: 1; }
.stat-label { font-size: 12px; color: #64748b; margin-top: 3px; }

.stat-card.blue   { border-left: 4px solid #3b82f6; }
.stat-card.blue   .stat-num { color: #1d4ed8; }
.stat-card.green  { border-left: 4px solid #10b981; }
.stat-card.green  .stat-num { color: #065f46; }
.stat-card.yellow { border-left: 4px solid #f59e0b; }
.stat-card.yellow .stat-num { color: #92400e; }
.stat-card.purple { border-left: 4px solid #8b5cf6; }
.stat-card.purple .stat-num { color: #4c1d95; }
.stat-card.teal   { border-left: 4px solid #06b6d4; }
.stat-card.teal   .stat-num { color: #164e63; }

.section-title {
  font-size: 15px; font-weight: 700; color: #1e293b;
  margin-bottom: 12px; margin-top: 8px;
}

.quick-actions {
  display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 28px;
}
.quick-btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 10px 20px; border-radius: 10px;
  font-size: 14px; font-weight: 600; text-decoration: none;
  transition: .2s;
}
.quick-btn.blue   { background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; }
.quick-btn.green  { background: #f0fdf4; color: #065f46; border: 1.5px solid #bbf7d0; }
.quick-btn.yellow { background: #fffbeb; color: #92400e; border: 1.5px solid #fde68a; }
.quick-btn.purple { background: #f5f3ff; color: #4c1d95; border: 1.5px solid #ddd6fe; }
.quick-btn:hover  { filter: brightness(.95); transform: translateY(-1px); }

.loading-box, .empty-box {
  padding: 40px; text-align: center; color: #94a3b8; font-size: 14px;
}
.status-tag {
  display: inline-block; padding: 5px 14px;
  border-radius: 20px; font-size: 13px; font-weight: 700;
}
.status-tag.pending   { background: #ffe100; color: #713f12; border: 1.5px solid #eab308; }
.status-tag.approved  { background: #00ff59; color: #14532d; border: 1.5px solid #22c55e; }
.status-tag.rejected  { background: #fecaca; color: #7f1d1d; border: 1.5px solid #ef4444; }
.status-tag.cancelled { background: #e2e8f0; color: #334155; border: 1.5px solid #94a3b8; }
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 16px; margin-bottom: 28px;
}
</style>