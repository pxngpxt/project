<template>
  <div>
    <div class="page-header">
      <h1>🏢 ห้องทั้งหมด</h1>
      <p>ค้นหาและดูข้อมูลห้องที่ต้องการจอง</p>
    </div>

    <div class="card filter-card">
      <div class="filter-row">
        <div class="form-group mb-0 flex-1">
          <input v-model="search" type="text" class="form-control"
            placeholder="🔍 ค้นหาห้อง, รหัสห้อง, สถานที่..." @input="loadRooms" />
        </div>
        <div class="form-group mb-0">
          <select v-model="filterStatus" class="form-control" @change="loadRooms">
            <option value="">ทุกสถานะ</option>
            <option value="Available">ว่าง</option>
            <option value="Unavailable">ไม่ว่าง</option>
            <option value="Maintenance">ซ่อมบำรุง</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading-box">⏳ กำลังโหลด...</div>
    <div v-else-if="rooms.length === 0" class="empty-box">ไม่พบห้องที่ค้นหา</div>

    <div v-else class="rooms-grid">
      <div v-for="room in rooms" :key="room.room_id" class="room-card">
        <div class="room-img">
          <span class="room-emoji">🏫</span>
          <span :class="statusBadgeClass(room.status)" class="room-status-badge">
            {{ statusLabel(room.status) }}
          </span>
        </div>
        <div class="room-body">
          <div class="room-code">{{ room.room_code }}</div>
          <h3 class="room-name">{{ room.room_name }}</h3>
          <div class="room-meta">
            <span>📍 {{ room.location }}</span>
            <span>👥 {{ room.capacity }} คน</span>
          </div>
          <div class="room-actions">
            <router-link
              v-if="room.status === 'Available'"
              :to="{ name: 'booking', query: { room_id: room.room_id, room_name: room.room_name } }"
              class="btn btn-primary btn-sm">
              📅 จองห้องนี้
            </router-link>
            <span v-else class="btn btn-sm unavailable-btn">ไม่พร้อมใช้งาน</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      rooms: [], loading: true, search: '', filterStatus: ''
    }
  },
  mounted() { this.loadRooms() },
  methods: {
    async loadRooms() {
      this.loading = true
      try {
        const res = await axios.get('http://localhost/project/api_php/rooms.php', {
          params: { search: this.search, status: this.filterStatus }
        })
        if (res.data.success) this.rooms = res.data.data
      } catch { /* silent */ }
      finally { this.loading = false }
    },
    statusLabel(s) {
      return { Available: 'ว่าง', Unavailable: 'ไม่ว่าง', Maintenance: 'ซ่อมบำรุง' }[s] || s
    },
    statusBadgeClass(s) {
      return {
        Available:   'room-badge available',
        Unavailable: 'room-badge unavailable',
        Maintenance: 'room-badge maintenance'
      }[s]
    }
  }
}
</script>

<style scoped>
.filter-card { padding: 16px; margin-bottom: 24px; }
.filter-row  { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
.flex-1      { flex: 1; min-width: 200px; }
.mb-0        { margin-bottom: 0 !important; }

.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 20px;
}

.room-card {
  background: white; border-radius: 14px; overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,.07); border: 1px solid #e2e8f0;
  transition: .25s;
}
.room-card:hover { transform: translateY(-4px); box-shadow: 0 10px 28px rgba(0,0,0,.12); }

.room-img {
  height: 130px; background: linear-gradient(135deg, #dbeafe, #eff6ff);
  display: flex; align-items: center; justify-content: center;
  position: relative;
}
.room-emoji { font-size: 52px; }

/* Status Badge บนรูป */
.room-status-badge {
  position: absolute; top: 12px; right: 12px;
  padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;
}
.room-badge.available   { background: #bbf7d0; color: #14532d; border: 1.5px solid #22c55e; }
.room-badge.unavailable { background: #fecaca; color: #7f1d1d; border: 1.5px solid #ef4444; }
.room-badge.maintenance { background: #fef08a; color: #713f12; border: 1.5px solid #eab308; }

.room-body { padding: 16px; }
.room-code { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
.room-name { font-size: 16px; font-weight: 700; margin: 4px 0 10px; }
.room-meta { display: flex; flex-direction: column; gap: 4px; font-size: 13px; color: #64748b; margin-bottom: 14px; }
.room-actions { display: flex; }

.unavailable-btn {
  background: #f1f5f9; color: #94a3b8; cursor: not-allowed; font-size: 13px;
}

.loading-box, .empty-box { text-align: center; padding: 60px; color: #94a3b8; }
</style>