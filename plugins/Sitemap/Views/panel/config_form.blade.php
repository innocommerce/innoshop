@extends('panel::layouts.app')

@section('title', $name)
@push('header')
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
@endpush
@section('content')

    <div class="mb-5" id="app">
        <el-tabs v-model="activeName" @tab-click="switchTab">
            <el-tab-pane :label="type+'-sitemap'" :name="index" v-for="(type,index) in types" :key="index">
                <ol class="list-group list-group-numbered lh-lg text-secondary">
                    <li>
                        sitemap文件地址：@{{sitemaps_url}} <a target="_blank" :href="sitemaps_url">预览</a>
                    </li>
                    <li>
                        如果需要在urlset节点中加入属性和值(默认加入google的，因此如果是给google使用，直接使用上面的地址就可以，根据具体情况来处理)，可以使用参数，
                        如：@{{sitemaps_url}}
                        ?urlset_type=google&urlset_attr_key=xmlns&urlset_attr_val={{urlencode('http://www.sitemaps.org/schemas/sitemap/0.9')}}
                    </li>
                </ol>
                <el-row v-if="dataPage.type == 'other'">
                    <el-col :span="6">
                        <el-button style="margin-bottom: 10px" type="danger" @click="addRow">新增一条</el-button>
                    </el-col>
                </el-row>
                <br/>
                <el-table
                    :data="sitemaps"
                    style="width: 100%">
                    <el-table-column type="expand">
                        <template slot-scope="props">
                            <div style="padding: 20px" v-if="props.row.type!='other'">
                                <el-descriptions title="链接信息">
                                    <el-descriptions-item label="类型">
                                    </el-descriptions-item>
                                    <el-descriptions-item label="页面标题">
                                        @{{ props.row.name }}
                                    </el-descriptions-item>
                                    <el-descriptions-item label="原始地址">
                                        @{{ props.row.url }}
                                    </el-descriptions-item>
                                    <!--
                                                  <el-descriptions-item label="Meta title">
                                                    @{{ props.row.description.meta_title }}
                                                  </el-descriptions-item>
                                                  <el-descriptions-item label="Meta keywords">
                                                    @{{ props.row.description.meta_keywords }}
                                                  </el-descriptions-item>
                                                  <el-descriptions-item label="Meta description">
                                                    @{{ props.row.description.meta_description }}
                                                  </el-descriptions-item>
                                                  -->
                                    <el-descriptions-item label="修改">
                                        <el-link type="warning" :href="props.row.edit_url" target="_blank">前往修改数据
                                        </el-link>
                                    </el-descriptions-item>
                                </el-descriptions>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="名称"
                        prop="id">
                        <template slot-scope="scope">
        <span v-if="scope.row.type == 'other'">
            <el-input v-model="scope.row.name" placeholder="请输入页面名字"></el-input>
          </span>
                            <span v-else>
            @{{ scope.row.name }}
          </span>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="URL">
                        <template slot-scope="scope">
         <span v-if="scope.row.type == 'other'">
            <el-input v-model="scope.row.loc" placeholder="请输入完整网页地址"></el-input>
          </span>
                            <span v-else>
            @{{ scope.row.loc }}
          </span>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="权重">
                        <template slot-scope="scope">
                            <el-input-number v-model="scope.row.priority"></el-input-number>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="状态">
                        <template slot-scope="scope">
                            <el-select v-model="scope.row.status" placeholder="请选择">
                                <el-option
                                    key="0"
                                    label="禁止生成"
                                    value="0">
                                </el-option>
                                <el-option
                                    key="1"
                                    label="允许生成"
                                    value="1">
                                </el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="操作">
                        <template slot-scope="scope">

                            <el-button @click="save(scope.row)">保存</el-button>
                            <el-button v-if="scope.row.type=='other'" @click="del(scope.row)" type="danger">删除
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="Pagination">
                    <el-pagination
                        @current-change="handleDataPageChange"
                        :current-page="dataPage.page"
                        :page-size="dataPage.pageSize"
                        layout="total, prev, pager, next"
                        :total="dataPage.total">
                    </el-pagination>
                </div>
            </el-tab-pane>

            <!--
            <el-tab-pane label="Google feed" name="google_feed">
                <ol class="list-group list-group-numbered lh-lg text-secondary">
                    <li>
                        google feed 地址：@{{google_feed_url}} <a target="_blank" :href="google_feed_url">预览</a>
                    </li>

                </ol>
                <br>
                <el-table
                    :data="google_feeds"
                    style="width: 100%">
                    <el-table-column
                        label="商品名称"
                        prop="name">
                    </el-table-column>
                    <el-table-column
                        label="GoogleFeed生成"
                        prop="google_feed_statusStr"
                    >
                    </el-table-column>
                    <el-table-column
                        label="商品URL"
                        prop="url"
                    >
                    </el-table-column>
                    <el-table-column
                        label="GTIN">
                        <template slot-scope="scope">
                            <el-input v-model="scope.row.google_feed_gtin"></el-input>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="使用情况">
                        <template slot-scope="scope">
                            <el-select v-model="scope.row.google_feed_condition" placeholder="请选择">
                                <el-option
                                    key="new"
                                    label="全新"
                                    value="new">
                                </el-option>
                                <el-option
                                    key="refurbished"
                                    label="翻新"
                                    value="refurbished">
                                </el-option>
                                <el-option
                                    key="used"
                                    label="二手"
                                    value="used">
                                </el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="生成状态">
                        <template slot-scope="scope">
                            <el-select v-model="scope.row.google_feed_status" placeholder="请选择">
                                <el-option
                                    key="0"
                                    label="禁止生成"
                                    value="0">
                                </el-option>
                                <el-option
                                    key="1"
                                    label="允许生成"
                                    value="1">
                                </el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="操作">
                        <template slot-scope="scope">
                            <el-button @click="saveGoogleFeed(scope.row)">保存</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="Pagination">
                    <el-pagination
                        @current-change="handleDataPageChange"
                        :current-page="dataPage.page"
                        :page-size="dataPage.pageSize"
                        layout="total, prev, pager, next"
                        :total="dataPage.total">
                    </el-pagination>
                </div>
            </el-tab-pane>
-->

        </el-tabs>
    </div>


@endsection
@push("footer")
    <!-- import Vue before Element -->
    <script src="https://unpkg.com/vue@2/dist/vue.js"></script>
    <!-- import JavaScript -->
    <script src="https://unpkg.com/element-ui/lib/index.js"></script>
    <script>

        let app = new Vue({
            el: '#app',

            data: {
                google_feed_url: "",
                sitemaps_url: "",
                sitemaps: [],
                google_feeds: [],
                rules: {},
                type:@json($type),
                types: @json($types),
                activeName: "products",
                dataPage: {
                    page: 1,
                    pageSize: 20,
                    total: 0,
                    type: "products"
                },

            },

            computed: {}
            ,
            created() {
                this.dataPage.type = this.activeName;
                if (this.activeName == 'google_feed') {
                    this.getGoogleFeeds();
                } else {
                    this.getSitemaps();
                }
            },
            methods: {
                addRow() {
                    this.sitemaps.unshift({
                        "id": 0,
                        'loc': "",
                        'lastmod': "",
                        'priority': 0.5,
                        'status': "1",
                        'type': 'other',
                        'type_id': 0
                    });
                }
                ,
                save(row) {
                    axios.put("{{ panel_route('sitemap') }}", row).then((res) => {
                        layer.msg(res.message)
                        this.dataPage.type = this.activeName;
                        if (this.activeName == 'google_feed') {
                            this.getGoogleFeeds();
                        } else {
                            this.getSitemaps();
                        }
                    })
                }
                ,

                del(row) {
                    layer.confirm("确定要删除这条URL吗", {
                        title: "{{ __('common.text_hint') }}",
                        btn: ['{{ __('common.cancel') }}', '{{ __('common.confirm') }}'],
                        area: ['400px'],
                        btn2: () => {
                            axios.delete("{{ panel_route('sitemap') }}", {'id': row.id}).then((res) => {
                                layer.msg(res.message)
                                window.location.reload();
                            })
                        }
                    })
                }
                ,
                getGoogleFeeds() {
                    let that = this;
                    axios.get("{{panel_route('seo_index.google_feeds')}}", {params: this.dataPage}).then((res) => {
                        console.log(res);
                        that.google_feed_url = res.google_feed_url;
                        that.google_feeds = res.google_feeds.data;
                        that.dataPage.page = res.google_feeds.current_page;
                        that.dataPage.pageSize = res.google_feeds.per_page;
                        that.dataPage.total = res.google_feeds.total;

                    })
                },
                handleDataPageChange(val) {
                    this.dataPage.page = val;
                    this.dataPage.offset = (val - 1) * this.dataPage.pageSize;
                    this.dataPage.type = this.activeName;
                    if (this.activeName == 'google_feed') {
                        this.getGoogleFeeds();
                    } else {
                        this.getSitemaps();
                    }
                },
                saveGoogleFeed(row) {
                    axios.put("{{ panel_route('google_feed') }}", row).then((res) => {
                        layer.msg(res.message)
                        if (row.google_feed_status == "1") {
                            row.google_feed_statusStr = "生成中"
                        } else {
                            row.google_feed_statusStr = "未生成"
                        }
                        //window.location.reload();
                    })
                },


                getSitemaps() {
                    let that = this;
                    axios.get("{{panel_route('seo_index.site_map')}}", {params: this.dataPage}).then((res) => {
                        console.log(res);
                        that.sitemaps = res.data;
                        that.dataPage.page = res.sitemapurls.current_page;
                        that.dataPage.pageSize = res.sitemapurls.per_page;
                        that.dataPage.total = res.sitemapurls.total;
                        that.sitemaps_url = res.sitemaps_url;
                    })
                },

                searchData() {
                    window.location.href = "{{panel_route("seo_index")}}?type=" + this.type
                },
                switchTab(tab, event) {
                    this.dataPage.page = 1;
                    this.dataPage.pageSize = 20;
                    console.log(this.activeName);
                    this.dataPage.type = this.activeName;
                    if (this.activeName == 'google_feed') {
                        this.getGoogleFeeds();
                    } else {
                        this.getSitemaps();
                    }
                }
            }
        })
    </script>
@endpush
