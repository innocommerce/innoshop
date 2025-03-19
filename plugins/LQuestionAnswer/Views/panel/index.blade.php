@extends('panel::layouts.app')

@section('title', $name)
@push('header')
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
@endpush
@section('content')
  <input type="hidden" value="{{csrf_token()}}" id="token">
  <div id="app">
    <el-row>
      <el-col :span="2">
        <el-button size="small" @click="openAddAskAnswerDialog(null,null)">新增问答</el-button>
      </el-col>
    </el-row>
    <br>
    <br>

    <div>
      <el-table
        :data="tableData"
        style="width: 100%;margin-bottom: 20px;"
        row-key="id"
        border
        default-expand-all
        :indent="36"
        :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
        <el-table-column
          prop="type"
          label="类型">
          <template slot-scope="scope">
            <span v-text="scope.row.type"></span>
          </template>

        </el-table-column>
        <el-table-column label="作者">
          <template slot-scope="scope">
            <el-avatar :src="scope.row.avatar"></el-avatar>
            <br>
            <span v-text="scope.row.user_name"></span>
          </template>
        </el-table-column>
        <el-table-column label="商品" width="180">
          <template slot-scope="scope">
            <div>
              <el-avatar :src="scope.row.product_img.path"></el-avatar>
              <br>
              <span v-text="scope.row.product_name"></span>
            </div>
          </template>
        </el-table-column>

        <el-table-column
          prop="content"
          width="380"
          label="内容">
          <template slot-scope="scope">
            <span v-if="scope.row.reply_user_name!=''">
            <span style="color:blue" v-text="'@'+scope.row.reply_user_name"></span><br>
              </span>
            <span v-text="scope.row.content"></span>
            <br>
            发布时间：<span v-text="scope.row.created_at"></span>
          </template>
        </el-table-column>
        <el-table-column
          prop="agree"
          label="赞同数">
        </el-table-column>
        <el-table-column
          prop="not_agree"
          label="不赞同数">
        </el-table-column>
        <el-table-column
          prop="not_agree"
          label="状态">
          <template slot-scope="scope">
            <span v-if="scope.row.status == 1" style="color: red">待审核</span>
            <span v-if="scope.row.status == 2" style="color: green">显示中</span>
            <span v-if="scope.row.status == 3" style="color: grey">隐藏</span>
          </template>
        </el-table-column>
        <el-table-column width="200" label="审核">
          <template slot-scope="scope">
            <div>
              <el-button size="mini" type="primary" @click="openAddAskAnswerDialog(scope.row,null)">编辑
              </el-button>
              <br>
              <br>
              <el-button size="mini" type="success" @click="openAddAskAnswerDialog(null,scope.row)">回复
              </el-button>
              <br><br>
              <el-button size="mini" type="warning"
                         v-if="scope.row.status == 1 || scope.row.status == 3"
                         @click="changeStatus(scope.row,2)">显示
              </el-button>
              <el-button size="mini" type="danger"
                         v-if="scope.row.status == 1 || scope.row.status == 2"
                         @click="changeStatus(scope.row,3)">隐藏
              </el-button>
              <br>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <div class="Pagination">
      <el-pagination
        @current-change="handlePageChange"
        :current-page="askAnswerPage.page"
        :page-size="askAnswerPage.pageSize"
        layout="total, prev, pager, next"
        :total="askAnswerPage.total">
      </el-pagination>
    </div>

    <el-dialog :title="dialog.title" :visible.sync="dialog.visible">
      <el-form label-position="top" :model="ask_answer" ref="addForm" :rules="rules">
        <el-form-item label="商品ID" prop="product_id" v-if="show_product_id">
          <el-input-number v-model="ask_answer.product_id" :min="1" :step="1" :precision="0"></el-input-number>
        </el-form-item>
        <el-form-item label="{{__("LQuestionAnswer::common.author")}}" prop="user_name">
          <el-input v-model="ask_answer.user_name" :minlength="3" :maxlength="50"
                    show-word-limit></el-input>
        </el-form-item>
        <el-form-item :label="content_label" prop="content_text">
          <el-input v-model="ask_answer.content_text" type="textarea" :minlength="10" :maxlength="350"
                    show-word-limit
                    :autosize="{minRows:4,maxRows:6}"></el-input>
        </el-form-item>
        <el-form-item label="{{__("LQuestionAnswer::common.agree")}}" prop="agree">
          <el-input-number v-model="ask_answer.agree" :min="0" :step="1" :precision="0"></el-input-number>
        </el-form-item>
        <el-form-item label="{{__("LQuestionAnswer::common.not_agree")}}" prop="not_agree">
          <el-input-number v-model="ask_answer.not_agree" :min="0" :step="1" :precision="0"></el-input-number>
        </el-form-item>
        <el-form-item>
          <el-button @click="dialog.visible=false">取消</el-button>
          <el-button type="primary" @click="saveAskAnswer">确定</el-button>
        </el-form-item>
      </el-form>

    </el-dialog>
  </div>

  <!-- import Vue before Element -->
  <script src="https://unpkg.com/vue@2/dist/vue.js"></script>
  <!-- import JavaScript -->
  <script src="https://unpkg.com/element-ui/lib/index.js"></script>
  <script>
    let app = new Vue({
      el: '#app',
      data: function () {
        return {
          askAnswerPage: {
            page: 1,
            pageSize: 20,
            q: '',
            total: 0,
          },
          dialog: {
            visible: false,
            title: ""
          },
          tableData: [],
          ask_answer: {
            id: 0,
            product_id: 0,
            reply_id: 0,
            user_name: "",
            content_text: "",
            agree: 0,
            not_agree: 0,
            status: 1,
            parent_id: 0
          },
          show_product_id: false,
          content_label: "提问内容",
          rules: {
            product_id: [
              {required: true, message: '请输入商品ID', trigger: 'blur'},
            ],
            user_name: [
              {required: true, message: '请输入作者', trigger: 'change'},
              {min: 2, max: 30, message: '长度在 2 到 30 个字符', trigger: 'blur'}
            ],
            content_text: [
              {required: true, message: '请输入内容', trigger: 'change'},
              {min: 10, max: 350, message: '长度在 10 到 350 个字符', trigger: 'blur'}
            ],
          }
        }
      },
      created() {
        let token = $("#token").val();
        this.getAskAnswers();
      },
      methods: {

        handleClick(tab, event) {
          console.log(tab, event);
        },
        openAddAskAnswerDialog(row, parentRow) {


          if (row == null) {
            if (parentRow != null) {//回复时
              this.show_product_id = false;
              this.dialog.title = "回复:" + parentRow.user_name
              this.content_label = '{{__("LQuestionAnswer::common.content_answer")}}'
            } else {
              this.content_label = '{{__("LQuestionAnswer::common.content_ask")}}'
              this.show_product_id = true;
              this.dialog.title = "新增问题"
            }
            console.log("add");
            this.ask_answer = {
              id: 0,
              product_id: parentRow == null ? 0 : parentRow.product_id,
              reply_id: parentRow == null ? 0 : parentRow.id,
              user_name: "",
              content_text: "",
              agree: 0,
              not_agree: 0,
              status: 1,
              parent_id: parentRow == null ? 0 : parentRow.parent_id == 0 ? parentRow.id : parentRow.parent_id,
            };

          } else {
            if (row.customer_id != 0) {//回复时
              this.show_product_id = false;
            } else {
              this.show_product_id = true;
            }
            if (row.parent_id == 0) {//回复时
              this.dialog.title = "修改问题"
              this.content_label = '{{__("LQuestionAnswer::common.content_ask")}}'
            } else {
              this.content_label = '{{__("LQuestionAnswer::common.content_answer")}}'
              this.dialog.title = "修改回复"
            }

            this.ask_answer = {
              id: row.id,
              product_id: row.product_id,
              user_name: row.user_name,
              content_text: row.content,
              agree: row.agree,
              not_agree: row.not_agree,
              status: row.status,
              parent_id: row.parent_id
            };
          }
          this.dialog.visible = true;
        },
        saveAskAnswer() {
          let that = this;
          this.$refs['addForm'].validate((valid) => {
            if (valid) {
              if (this.ask_answer.id > 0) {
                axios.put('{{panel_route('ask_answer.update')}}', this.ask_answer).then((res) => {
                  if (res.code == 0) {
                    that.$message.success("保存成功");
                    that.getAskAnswers();
                  } else {
                    layer.msg(res.msg);
                  }
                })
              } else {
                axios.post('{{panel_route('ask_answer.store')}}', this.ask_answer).then((res) => {
                  if (res.code == 0) {
                    that.$message.success("保存成功");
                    that.dialog.visible = false;
                    that.getAskAnswers();
                  } else {
                    layer.msg(res.msg);
                  }
                })
              }
            }
          });
        },
        changeStatus(row, status) {
          let that = this;
          axios.put('{{panel_route('ask_answer.status')}}', {id: row.id, status: status}).then((res) => {
            if (res.code == 0) {
              that.$message.success("更新成功");
              that.getAskAnswers();
            } else {
              layer.msg(res.msg);
            }
          })
        }
        ,

        getAskAnswers() {
          let that = this;
          axios.get('{{panel_route('ask_answer.list')}}', this.askAnswerPage).then((res) => {
            console.log(res);
            that.tableData = res.ask_answers.data;
            that.askAnswerPage.page = res.ask_answers.current_page;
            that.askAnswerPage.pageSize = res.ask_answers.per_page;
            that.askAnswerPage.total = res.ask_answers.total;


          })
        }
        ,

        handlePageChange(val) {
          this.askAnswerPage.page = val;
          this.askAnswerPage.offset = (val - 1) * this.askAnswerPage.pageSize;
          this.getAskAnswers()
        }
        ,


        delQuestionAnswer(row) {
          let that = this;
          let tip = '确定要将这条问答删除吗？'
          layer.confirm(tip, {
            title: "{{ __('common.text_hint') }}",
            btn: ['{{ __('common.cancel') }}', '{{ __('common.confirm') }}'],
            area: ['400px'],
            btn2: () => {
              axios.delete("{{panel_route('ask_answer.update')}}", {'id': row.id}).then((res) => {
                if (res.code == 0) {
                  layer.msg(res.msg)
                  that.getAskAnswers()
                } else {
                  layer.msg(res.msg)
                }
              })
            }
          })
        }
        ,

      }
    })
  </script>
  <style>
    .Pagination {
      display: flex;
      justify-content: flex-start;
      margin-top: 8px;
    }
  </style>

  <style>
    .demo-table-expand {
      font-size: 0;
      margin-left: 10px;
    }

    .demo-table-expand label {
      width: 90px;
      color: #99a9bf;
    }

    .demo-table-expand .el-form-item {
      margin-right: 0;
      margin-bottom: 0;
      width: 50%;
    }
  </style>
@endsection
