<template>
  <div class="app-container">
    <el-card>
      <div slot="header" class="clearfix">
        <span>{{ $t('{extensionTitle}数据') }}</span>
      </div>

      <div class="filter-container">
        <el-row :gutter="10">
          <el-col :md="6" :sm="8">
            <div>
              <el-input v-model="listQuery.searchword" :placeholder="$t('请输入关键字')" clearable class="filter-item" @keyup.enter.native="handleFilter" />
            </div>
          </el-col>
          <el-col :md="5" :sm="7">
            <div>
              <el-date-picker v-model="listQuery.start_time" format="yyyy-MM-dd HH:mm:ss" type="datetime" placeholder="选择开始时间" clearable style="width: 100%;" class="filter-item" />
           </div>            
          </el-col> 
          <el-col :md="5" :sm="7">
            <div>
              <el-date-picker v-model="listQuery.end_time" format="yyyy-MM-dd HH:mm:ss" type="datetime" placeholder="选择结束时间" clearable style="width: 100%;" class="filter-item" />       
            </div>            
          </el-col>                            
        </el-row>

        <el-row :gutter="10">
          <el-col :md="3" :sm="3">
            <div>
              <el-button
                v-waves 
                class="filter-item"
                type="danger"
                style="width:100%;"
                :disabled="!showDeletebtn"
                @click="handleDeleteList"
              >
                {{ $t('删除选中') }}
              </el-button>       
            </div>
          </el-col>
          <el-col :md="3" :sm="3">
            <div>
              <el-select v-model="listQuery.status" :placeholder="$t('状态')" clearable class="filter-item" style="width: 100%;">
                <el-option v-for="item in statusOptions" :key="item.key" :label="item.display_name" :value="item.key" />
              </el-select>
            </div>            
          </el-col>   
          <el-col :md="3" :sm="3">
            <div>
              <el-select v-model="listQuery.order" class="filter-item" @change="handleFilter" style="width: 100%;">
                <el-option v-for="item in sortOptions" :key="item.key" :label="item.label" :value="item.key" />
              </el-select>   
            </div>            
          </el-col> 
          <el-col :md="3" :sm="3">
            <div>
              <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter" style="width: 100%;">
                {{ $t('搜索') }}
              </el-button>
            </div>            
          </el-col>            
        </el-row>
      </div>

      <el-table
        ref="logTable"
        v-loading="listLoading"
        :header-cell-style="{background:'#eef1f6',color:'#606266'}"
        :data="list"
        class="border-gray"
        fit
        highlight-current-row
        style="width: 100%"
        @selection-change="handleSelectionChange"
      >
        <el-table-column
          type="selection"
          width="55"
          align="center"
        />

        <el-table-column width="100px" align="left" :label="$t('ID')">
          <template slot-scope="{row}">
            <span>{{ row.id }}</span>
          </template>
        </el-table-column>

        <el-table-column min-width="150px" :label="$t('标题')">
          <template slot-scope="{row}">
            <span>{{ row.title }}</span>
          </template>
        </el-table-column>

        <el-table-column min-width="150px" :label="$t('描述')">
          <template slot-scope="{row}">
            <span>{{ row.desc }}</span>
          </template>
        </el-table-column>

        <el-table-column width="170px" align="left" :label="$t('请求时间')">
          <template slot-scope="scope">
            <span class="text-muted">
              <i class="el-icon-time" />&nbsp;
              {{ scope.row.time | parseTime('{y}-{m}-{d} {h}:{i}:{s}') }}
            </span>
          </template>
        </el-table-column>

        <el-table-column class-name="status-col" :label="$t('状态')" width="70">
          <template slot-scope="{row}">
            <el-tag :type="row.status | statusFilter" size="mini">
              {{ (row.status == 1) ? $t('启用') : $t('禁用') }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column align="center" :label="$t('操作')" width="200">
          <template slot-scope="scope">
            <el-button 
              v-waves
              :loading="scope.row.id == loading.detail"
              :disabled="!checkPermission(['larke-admin.ext.demo.detail'])" 
              type="info" 
              size="mini" 
              icon="el-icon-info" 
              @click="handleDetail(scope.$index, scope.row)"
            >
              {{ $t('详情') }}
            </el-button>

            <el-button 
              v-waves
              :loading="scope.row.id == loading.delete"
              v-permission="['larke-admin.ext.demo.delete']" 
              type="danger" 
              size="mini" 
              icon="el-icon-delete" 
              @click="handleDelete(scope.$index, scope.row)"
            >
              {{ $t('删除') }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <pagination 
        v-show="total>0" 
        :total="total" 
        :page.sync="listQuery.page" 
        :limit.sync="listQuery.limit" 
        @pagination="getList" 
      />
    </el-card>

    <el-dialog 
        :title="$t('数据详情')" 
        :visible.sync="detail.dialogVisible"
    >
      <detail :data="detail.data" />
    </el-dialog>
  </div>
</template>

<script>
import waves from '@/directive/waves' // waves directive
import { parseTime } from '@/utils'
import permission from '@/directive/permission/index.js' // 权限判断指令
import checkPermission from '@/utils/permission' // 权限判断函数
import Pagination from '@/components/Pagination' // Secondary package based on el-pagination
import Detail from '@/components/Larke/Detail'
import { 
    getList, 
    getDetail, 
    deleteData, 
 } from '../api/index'

export default {
  name: 'AdminLogIndex',
  components: { Pagination, Detail },
  directives: { waves, permission },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: 'success',
        0: 'danger'
      }
      return statusMap[status]
    }
  },
  data() {
    return {
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        searchword: '',
        start_time: '',
        end_time: '',
        order: 'time__DESC',
        status: '',
        method: '',
        page: 1,
        limit: 20
      },
      statusOptions: [
        { key: 'open', display_name: this.$t('启用') },
        { key: 'close', display_name: this.$t('禁用') }
      ],
      sortOptions: [
        { key: 'time__ASC', label: this.$t('正序') },
        { key: 'time__DESC', label: this.$t('倒叙') }
      ],
      detail: {
        dialogVisible: false,
        data: []
      },
      selectedData: [],
      showDeletebtn: false,
      loading: {
        detail: '',
        delete: '',
      },
    }
  },
  created() {
    this.getList()
  },
  methods: {
    checkPermission,
    getList() {
      this.listLoading = true
      getList({
        searchword: this.listQuery.searchword,
        start_time: this.listQuery.start_time,
        end_time: this.listQuery.end_time,
        status: this.listQuery.status,
        order: this.listQuery.order,
        start: (this.listQuery.page - 1) * this.listQuery.limit,
        limit: this.listQuery.limit
      }).then(response => {
        this.list = response.data.list
        this.total = response.data.total
        this.listLoading = false
      })
    },
    handleFilter() {
      this.listQuery.page = 1
      this.getList()
    },
    handleSelectionChange(data, key) {
      this.selectedData = []
      data.forEach(element => {
        this.selectedData.push(element.id)
      })

      if (this.selectedData.length > 0) {
        this.showDeletebtn = true
      } else {
        this.showDeletebtn = false
      }
    },
    handleDetail(index, row) {
      this.loading.detail = row.id

      getDetail(row.id).then((res) => {
        this.detail.dialogVisible = true
        const data = res.data

        this.loading.detail = ''

        this.detail.data = [
          {
            name: this.$t('ID'),
            content: data.id,
            type: 'text'
          },
          {
            name: this.$t('标题'),
            content: data.title,
            type: 'text'
          },
          {
            name: this.$t('描述'),
            content: data.desc,
            type: 'text'
          },
          {
            name: this.$t('时间'),
            content: data.time,
            type: 'time'
          },
          {
            name: this.$t('状态'),
            content: data.status + "",
            type: 'boolen'
          }
        ]
      }).catch((err) => {
        this.loading.detail = ''
      })
    },
    handleDelete(index, row) {
      const thiz = this
      this.$confirm(this.$t('确认要删除该数据吗？'), this.$t('提示'), {
        confirmButtonText: this.$t('确定'),
        cancelButtonText: this.$t('取消'),
        type: 'warning'
      }).then(() => {
        thiz.loading.delete = row.id

        deleteData(row.id).then(res => {
          thiz.loading.delete = ''
          thiz.list.splice(index, 1)

          this.$message({
            message: res.message,
            type: 'success',
            duration: 3 * 1000
          })
        }).catch(() => {
          thiz.loading.delete = ''
        })
      }).catch(() => {})
    },
    handleDeleteList() {
      this.$confirm(this.$t('确认要删除选中的数据吗？'), this.$t('提示'), {
        confirmButtonText: this.$t('确定'),
        cancelButtonText: this.$t('取消'),
        type: 'warning'
      }).then(() => {
        if (this.selectedData.length < 1) {
          this.$message({
            message: this.$t('请选择要删除的数据'),
            type: 'error',
            duration: 3 * 1000
          })
          return
        }

        this.$message({
          message: this.$t('删除选择的数据成功'),
          type: 'success',
          duration: 3 * 1000,
          onClose() {
            for (let i = thiz.list.length - 1; i >= 0; i--) {
              if (thiz.selectedData.includes(thiz.list[i].id)) {
                thiz.list.splice(i, 1)
              }
            }
          }
        })
      }).catch(() => {

      })
    },
  }
}
</script>

<style scoped>
.pagination-container {
  padding: 5px 2px;
}
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
</style>
