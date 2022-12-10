import Layout from '@/layout'

// 日志
const route = {
  path: '/{extensionName}/index',
  component: Layout,
  redirect: '/{extensionName}/list',
  alwaysShow: true,
  name: '{extensionTitle}',
  meta: {
    title: '{extensionTitle}',
    icon: 'el-icon-document-add',
    roles: [
      'larke-admin.ext.{extensionName}.index',
    ]
  }, 
  sort: 101000,
  children: [
    {
      path: '/{extensionName}/list',
      component: () => import('./views/index'),
      name: 'DemoList',
      meta: {
        title: 'DemoList',
        icon: 'el-icon-document-add',
        roles: [
          'larke-admin.ext.{extensionName}.index'
        ]
      }
    },

  ]
}

export default route