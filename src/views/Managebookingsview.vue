<template>
  <div>
    <div class="page-header">
      <h1>✅ อนุมัติการจอง</h1>
      <p>จัดการคำขอจองห้องจากผู้ใช้งาน</p>
    </div>

    <div class="tab-bar">
      <button v-for="tab in tabs" :key="tab.value"
        :class="['tab-btn', { active: activeTab === tab.value }]"
        @click="activeTab = tab.value; loadBookings()">
        {{ tab.label }}
        <span v-if="tab.count" class="tab-count">{{ tab.count }}</span>
      </button>
    </div>

    <div v-if="loading" class="loading-box">⏳ กำลังโหลด...</div>
    <div v-else-if="bookings.length === 0" class="card empty-box">ไม่มีรายการ</div>

    <div v-else class="card table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>ผู้จอง</th>
            <th>ห้อง</th>
            <th>ช่วงเวลา</th>
            <th>วัตถุประสงค์</th>
            <th>สถานะ</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in bookings" :key="b.booking_id">
            <td class="text-muted">#{{ b.booking_id }}</td>
            <td>
              <div class="user-cell">
                <div class="mini-avatar">{{ b.first_name?.charAt(0) }}</div>
                <div>
                  <div class="font-bold">{{ b.first_name }} {{ b.last_name }}</div>
                  <div class="text-xs text-muted">{{ b.email }}</div>
                </div>
              </div>
            </td>
            <td>
              <div class="font-bold">{{ b.room_name }}</div>
              <div class="text-xs text-muted">{{ b.room_code }} · {{ b.location }}</div>
            </td>
            <td>
              <div class="time-cell">
                <div class="time-row"><span class="time-icon">▶</span> {{ formatDate(b.start_time) }}</div>
                <div class="time-row"><span class="time-icon">⏹</span> {{ formatDate(b.end_time) }}</div>
              </div>
            </td>
            <td>
              <div class="purpose-cell">{{ b.purpose }}</div>
            </td>
            <td><span :class="statusClass(b.status)">{{ statusLabel(b.status) }}</span></td>
            <td>
              <div class="action-btns" v-if="b.status === 'Pending'">
                <button @click="updateStatus(b, 'Approved')" class="btn btn-success btn-sm">✅ อนุมัติ</button>
                <button @click="updateStatus(b, 'Rejected')" class="btn btn-danger btn-sm">❌ ปฏิเสธ</button>
              </div>
              <span v-else class="text-muted text-sm">-</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      bookings: [], loading: true, activeTab: 'Pending',
      pendingCount: 0,
      user: JSON.parse(localStorage.getItem('user') || '{}')
    }
  },
  computed: {
    tabs() {
      return [
        { label: '⏳ รอดำเนินการ', value: 'Pending', count: this.pendingCount },
        { label: '✅ อนุมัติแล้ว', value: 'Approved', count: 0 },
        { label: '❌ ปฏิเสธ',      value: 'Rejected', count: 0 },
        { label: '📋 ทั้งหมด',     value: '',          count: 0 }
      ]
    }
  },
  mounted() { this.loadBookings() },
  methods: {
    async loadBookings() {
      this.loading = true
      try {
        const res = await axios.get('http://localhost/project/api_php/bookings.php', {
          params: { role_id: this.user.role_id, status: this.activeTab }
        })
        if (res.data.success) {
          this.bookings = res.data.data
          if (this.activeTab === 'Pending') this.pendingCount = this.bookings.length
        }
      } catch { /* silent */ }
      finally { this.loading = false }
    },
    async updateStatus(b, status) {
      const msg = status === 'Approved' ? 'อนุมัติ' : 'ปฏิเสธ'
      if (!confirm(`ยืนยัน${msg}การจองนี้?`)) return
      const res = await axios.put('http://localhost/project/api_php/bookings.php',
        { booking_id: b.booking_id, status })
      if (res.data.success) this.loadBookings()
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
    statusClass(s) {
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
.tab-bar { display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; }
.tab-btn {
  padding: 10px 18px; background: none; border: none; cursor: pointer;
  font-family: inherit; font-size: 14px; font-weight: 500; color: #64748b;
  border-bottom: 2px solid transparent; margin-bottom: -2px;
  transition: .2s; display: flex; align-items: center; gap: 6px;
}
.tab-btn.active { color: #1a56db; border-bottom-color: #1a56db; font-weight: 700; }
.tab-btn:hover  { color: #1a56db; }
.tab-count {
  background: #ef4444; color: white; border-radius: 20px;
  padding: 1px 7px; font-size: 11px; font-weight: 700;
}

.user-cell { display: flex; align-items: center; gap: 8px; }
.mini-avatar {
  width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
  background: linear-gradient(135deg, #1a56db, #0ea5e9);
  color: white; font-weight: 700; font-size: 13px;
  display: flex; align-items: center; justify-content: center;
}
.font-bold  { font-weight: 600; font-size: 14px; }
.text-xs    { font-size: 11px; }
.text-sm    { font-size: 13px; }
.text-muted { color: #94a3b8; }

.time-cell  { display: flex; flex-direction: column; gap: 4px; }
.time-row   { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #334155; }
.time-icon  { font-size: 11px; }

.purpose-cell { max-width: 200px; font-size: 13px; color: #475569; }
.action-btns  { display: flex; gap: 6px; }

/* Status Tags */
.status-tag {
  display: inline-block; padding: 5px 14px;
  border-radius: 20px; font-size: 13px; font-weight: 700;
}
.status-tag.pending   { background: #fef08a; color: #713f12; border: 1.5px solid #eab308; }
.status-tag.approved  { background: #bbf7d0; color: #14532d; border: 1.5px solid #22c55e; }
.status-tag.rejected  { background: #fecaca; color: #7f1d1d; border: 1.5px solid #ef4444; }
.status-tag.cancelled { background: #e2e8f0; color: #334155; border: 1.5px solid #94a3b8; }

.loading-box { text-align: center; padding: 40px; color: #94a3b8; }
.empty-box   { padding: 40px; text-align: center; color: #94a3b8; }
</style>