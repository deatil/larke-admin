import request from '@/utils/request'

export function getList(params) {
  return request({
    url: '/{extensionName}',
    method: 'get',
    params
  })
}

export function getDetail(id) {
  return request({
    url: `/{extensionName}/${id}`,
    method: 'get'
  })
}

export function deleteData(id) {
  return request({
    url: `/{extensionName}/${id}`,
    method: 'delete'
  })
}
