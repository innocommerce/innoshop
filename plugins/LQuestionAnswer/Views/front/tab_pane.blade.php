@push('header')
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
@endpush

<div class="tab-pane fade" id="product-ask_answers" role="tabpanel">

    <div v-if="typeof source.ask_answers !== 'undefined'">
        <div class="hbl-fa">
            <div class="hbl-comm" v-if="source.reply_id == '0'">
                <!--<div class="comment-avatar">
                  <avatar :avatar="avatar"></avatar>
                </div>-->
                <div class="comment" :style="{ width: source.commentWidth }">
                    <el-form label-position="right" label-width="80px" :model="source.ask_answer_obj"
                             ref="addAskAnswerForm_0"
                             :rules="source.ask_answer_rules">
                        <el-form-item label="{{__("LQuestionAnswer::common.author")}}"
                                      v-if="source.ask_answer_obj.show_user_name"
                                      prop="user_name">
                            <el-input v-model="source.ask_answer_obj.user_name"></el-input>
                        </el-form-item>
                        <el-form-item label="{{__("LQuestionAnswer::common.content_ask")}}" prop="content_text">
                            <el-input
                                type="textarea"
                                :autosize="{ minRows: 3, maxRows: 8 }"
                                placeholder=""
                                v-model="source.ask_answer_obj.content_text"
                            >
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-button type="success" size="mini"
                                       @click="submitReply('0')">{{__("LQuestionAnswer::common.btn_send")}}</el-button>
                            <el-button type="info" size="mini"
                                       @click="doReply('-1',null)">{{__("LQuestionAnswer::common.btn_cancel")}}</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>

            <div v-if="source.ask_answers.length == 0">
                <div style="width: 100%;text-align: center;margin-bottom: 10px" v-if="source.reply_id != 0">
                    <el-button type="primary"
                               @click="doReply('0',null)">{{__("LQuestionAnswer::common.add_first_question")}}</el-button>
                </div>
                <el-empty description="{{__("LQuestionAnswer::common.empty_data")}}"></el-empty>
            </div>
            <div v-if="source.ask_answers.length > 0">
                <div style="width: 100%;text-align: center;margin-bottom: 10px" v-if="source.reply_id != 0">
                    <el-button type="primary"
                               @click="doReply('0',null)">{{__("LQuestionAnswer::common.add_question")}}</el-button>
                </div>
            </div>

            <div class="comm" v-if="source.ask_answers.length > 0">
                <div class="su com-rep"></div>
                <div class="com-rep com-title">
                    {{__("LQuestionAnswer::common.question")}}<span class="com-span">(<span id="ask_answer_count2">{{$ask_answers_count}}</span>)</span>
                </div>
            </div>

            <div v-for="(item, index) in source.ask_answers" class="hbl-child" v-if="source.ask_answers.length > 0">
                <div class="reply"></div>
                <div class="content">
                    <div class="comment-f">
                        <el-avatar :src="item.user_avatar"></el-avatar>
                    </div>

                    <div class="comment-f">
                        <div>
                            <div class="nickname author">
                                @{{ item.user_name }}
                            </div>
                            <div v-if="item.is_verified == 1" class="icon author">
                                {{__("LQuestionAnswer::common.is_verified")}}
                            </div>
                            <div class="date">
                                @{{ item.created_at }}
                            </div>
                        </div>
                    </div>

                    <div class="reply-content">
                        @{{ item.content }}
                    </div>
                    <div class="reply-content reply-fa">
                        <div class="reply-font">
                            <div>

                                @if ($can_add_ask_answer)
                                    <img src="{{plugin_resize('l_question_answer','/reply.png',40,40)}}"
                                         class="icon-reply"
                                         @click="doReply(''+item.id,item)"/><span
                                        class="icon-reply icon-hf"
                                        @click="doReply(''+item.id,item)">{{__("LQuestionAnswer::common.btn_reply")}}</span>
                                @endif
                                <span style="float: right;">
                <span style="margin-right: 2vh">{{__("LQuestionAnswer::common.helpful")}}</span>
                <img src="{{plugin_resize('l_question_answer','/agree.png',40,40)}}" width="20px" height="20px"
                     @click="submitAgree(''+item.id,1)" v-if="item.current_agree == 0"/>
                <img src="{{plugin_resize('l_question_answer','/agree_red.png',40,40)}}" width="20px" height="20px"
                     @click="submitAgree(''+item.id,1)" v-if="item.current_agree == 1"/>
                <span style="margin-right: 1vh">@{{ item.agree }}</span>
                <img src="{{plugin_resize('l_question_answer','/not_agree.png',40,40)}}" width="20px" height="20px"
                     @click="submitAgree(''+item.id,2)" v-if="item.current_not_agree == 0"/>
                <img src="{{plugin_resize('l_question_answer','/not_agree_red.png',40,40)}}" width="20px" height="20px"
                     @click="submitAgree(''+item.id,2)" v-if="item.current_not_agree == 1"/>
                <span>@{{ item.not_agree }}</span>
                </span>
                            </div>

                        </div>

                        <div
                            class="comment"
                            :style="{ width: source.commentWidth }"
                            v-if="source.reply_id == ''+item.id"
                        >
                            <el-form label-position="right" label-width="80px" :model="source.ask_answer_obj"
                                     :ref="'addAskAnswerForm_'+item.id" :rules="source.ask_answer_rules">
                                <el-form-item label="{{__("LQuestionAnswer::common.author")}}"
                                              v-if="source.ask_answer_obj.show_user_name"
                                              prop="user_name">
                                    <el-input v-model="source.ask_answer_obj.user_name"></el-input>
                                </el-form-item>
                                <el-form-item label="{{__("LQuestionAnswer::common.content_answer")}}"
                                              prop="content_text">
                                    <el-input
                                        type="textarea"
                                        :autosize="{ minRows: 3, maxRows: 8 }"
                                        placeholder=""
                                        v-model="source.ask_answer_obj.content_text"
                                    >
                                    </el-input>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-button type="success" size="mini"
                                               @click="submitReply(''+item.id)">{{__("LQuestionAnswer::common.btn_send")}}</el-button>
                                    <el-button type="info" size="mini"
                                               @click="doReply('-1',null)">{{__("LQuestionAnswer::common.btn_cancel")}}</el-button>
                                </el-form-item>
                            </el-form>
                        </div>
                    </div>
                </div>

                <div class="reply-content reply-fa">
                    <div v-if="item.children.length > 0" @click="openChildrenReply(''+item.id)">
                        {{__("LQuestionAnswer::common.all_answer")}}(@{{ item.children.length }})
                    </div>
                </div>

                <div v-show="source.show_children_id == ''+item.id">
                    <div class="children" v-for="(ritem, jndex) in item.children" :key="ritem.id">
                        <div class="reply"></div>
                        <div class="content">
                            <div class="comment-f">
                                <el-avatar :src="ritem.user_avatar"></el-avatar>
                            </div>

                            <div class="comment-f">
                                <div>
                                    <div class="nickname author">
                                        @{{ ritem.user_name }}
                                    </div>
                                    <div v-if="ritem.is_verified == 1" class="icon author">
                                        {{__("LQuestionAnswer::common.is_verified")}}
                                    </div>
                                    <div class="date">
                                        @{{ ritem.created_at }}
                                    </div>
                                </div>
                            </div>

                            <div class="reply-content">
                                <div class="cc cc-to" v-if="ritem.reply_user_name != ''">
                                    <a href="#">@<span v-text="ritem.reply_user_name"></span></a>
                                </div>

                                <div class="cc">
                                    @{{ ritem.content }}
                                </div>
                            </div>

                            <div class="reply-content reply-fa">
                                <div class="reply-font">
                                    <div>
                                        @if ($can_add_ask_answer)
                                            <img src="{{plugin_resize('l_question_answer','/reply.png',40,40)}}"
                                                 @click="doReply(''+ritem.id,ritem)"
                                                 class="icon-reply"/><span
                                                @click="doReply(''+ritem.id,ritem)">{{__("LQuestionAnswer::common.btn_reply")}}</span>
                                        @endif
                                        <span style="float: right;">
                <span style="margin-right: 2vh">{{__("LQuestionAnswer::common.helpful")}}</span>
                <img src="{{plugin_resize('l_question_answer','/agree.png',40,40)}}" width="20px" height="20px"
                     @click="submitAgree(''+ritem.id,1)" v-if="ritem.current_agree == 0"/>
                <img src="{{plugin_resize('l_question_answer','/agree_red.png',40,40)}}" width="20px" height="20px"
                     @click="submitAgree(''+ritem.id,1)" v-if="ritem.current_agree == 1"/>
                <span style="margin-right: 1vh">@{{ ritem.agree }}</span>
                <img src="{{plugin_resize('l_question_answer','/not_agree.png',40,40)}}" width="20px" height="20px"
                     @click="submitAgree(''+ritem.id,2)" v-if="ritem.current_not_agree == 0"/>
                <img src="{{plugin_resize('l_question_answer','/not_agree_red.png',40,40)}}" width="20px" height="20px"
                     @click="submitAgree(''+ritem.id,2)" v-if="ritem.current_not_agree == 1"/>
                <span>@{{ ritem.not_agree }}</span>
                </span>
                                    </div>


                                </div>

                                <div
                                    class="comment"
                                    :style="{ width: source.commentWidth }"
                                    v-if="source.reply_id == ritem.id"
                                >
                                    <el-form label-position="right" label-width="80px" :model="source.ask_answer_obj"
                                             :ref="'addAskAnswerForm_'+ritem.id" :rules="source.ask_answer_rules">
                                        <el-form-item label="{{__("LQuestionAnswer::common.author")}}"
                                                      v-if="source.ask_answer_obj.show_user_name"
                                                      prop="user_name">
                                            <el-input v-model="source.ask_answer_obj.user_name"></el-input>
                                        </el-form-item>
                                        <el-form-item label="{{__("LQuestionAnswer::common.content_answer")}}"
                                                      prop="content_text">
                                            <el-input
                                                type="textarea"
                                                :autosize="{ minRows: 3, maxRows: 8 }"
                                                placeholder=""
                                                v-model="source.ask_answer_obj.content_text"
                                            >
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item label="">
                                            <el-button type="success" size="mini"
                                                       @click="submitReply(''+ritem.id)">{{__("LQuestionAnswer::common.btn_send")}}</el-button>
                                            <el-button type="info" size="mini"
                                                       @click="doReply('-1',null)">{{__("LQuestionAnswer::common.btn_cancel")}}</el-button>
                                        </el-form-item>
                                    </el-form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="Pagination" v-if="source.ask_answers.length > 0">
            <el-pagination
                @current-change="handleAskAnswerPageChange"
                :current-page="source.ask_answers_page.page"
                :page-size="source.ask_answers_page.pageSize"
                layout="total, prev, pager, next"
                :total="source.ask_answers_page.total"
            >
            </el-pagination>
        </div>

    </div>
</div>

@push("footer")


    <!-- import Vue before Element -->
    <script src="https://unpkg.com/vue@2/dist/vue.js"></script>
    <!-- import JavaScript -->
    <script src="https://unpkg.com/element-ui/lib/index.js"></script>
    <script>
        let app = new Vue({
            el: '#product-ask_answers',
            data: function () {
                return {
                    source: {},
                }
            },
            created() {

            },
            methods: {}
        })
    </script>
    <script>


        $(function () {

            let customer_name =  @json($customer?$customer->name:null);

            let show_user_name = true;
            let user_name = "";
            if (customer_name != null) {
                show_user_name = false;
                user_name = customer_name;
            }

            app.$set(app.source, "reply_id", '-1');
            app.$set(app.source, "show_children_id", '-1');

            app.$set(app.source, "commentWidth", '80%');

            let ask_answer_obj = {
                id: 0,
                product_id: @json($product['id']),
                reply_id: 0,
                show_user_name: show_user_name,
                user_name: user_name,
                content_text: "",
                agree: 0,
                not_agree: 0,
                parent_id: 0
            };
            app.$set(app.source, "ask_answer_obj", ask_answer_obj);

            let ask_answersDat = @json($ask_answers?$ask_answers:[]);
            app.$set(app.source, "ask_answers", ask_answersDat.data);
            let page = {
                product_id: @json($product['id']),
                page: ask_answersDat.current_page,
                pageSize: ask_answersDat.per_page,
                total: ask_answersDat.total,
            };
            app.$set(app.source, "ask_answers_page", page);


            app.$set(app.source, "ask_answer_rules", {
                user_name: [
                    {required: true, message: '{{__("LQuestionAnswer::common.rules_empty")}}', trigger: 'blur'},
                    {
                        min: 2,
                        max: 30,
                        message: '{{__("LQuestionAnswer::common.rules_limit",["min"=>2,"max"=>30])}}',
                        trigger: 'blur'
                    }
                ],
                content_text: [
                    {required: true, message: '{{__("LQuestionAnswer::common.rules_empty")}}', trigger: 'blur'},
                    {
                        min: 10,
                        max: 350,
                        message: '{{__("LQuestionAnswer::common.rules_limit",["min"=>10,"max"=>350])}}',
                        trigger: 'blur'
                    }
                ],
            });

            app.handleAskAnswerPageChange = function (val) {

                app.source.ask_answers_page.page = val;
                app.source.ask_answers_page.offset = (val - 1) * app.source.ask_answers_page.pageSize;
                app.getAskAnswers()
            };

            app.getAskAnswers = function () {
                axios.get('{{front_route('ask_answer.list')}}', {params:app.source.ask_answers_page}).then((res) => {
                    console.log(res);
                    app.source.ask_answers = res.data.ask_answers.data;
                    console.log(res.data.ask_answers);
                    app.source.ask_answers_page.page = res.data.ask_answers.current_page;
                    app.source.ask_answers_page.pageSize = res.data.ask_answers.per_page;
                    app.source.ask_answers_page.total = res.data.ask_answers.total;
                    $("#ask_answer_count").text(res.data.ask_answers_count);
                    $("#ask_answer_count2").text(res.data.ask_answers_count);
                })
            };

            app.doReply = function (id, parentRow) {
                if (app.source.reply_id == id) {
                    app.source.reply_id = '-1'
                } else {
                    app.source.reply_id = id;
                    if (parentRow == null) {
                        app.source.ask_answer_obj.parent_id = 0;
                        app.source.ask_answer_obj.reply_id = 0;
                        app.source.ask_answer_obj.user_name = user_name;
                        app.source.ask_answer_obj.content_text = "";
                    } else {
                        app.source.ask_answer_obj.parent_id = parentRow.parent_id == 0 ? parentRow.id : parentRow.parent_id;
                        app.source.ask_answer_obj.reply_id = parentRow.id;
                        app.source.ask_answer_obj.user_name = user_name;
                        app.source.ask_answer_obj.content_text = "";
                    }
                }
            }
            app.openChildrenReply = function (id) {
                if (app.source.show_children_id == id) {
                    app.source.show_children_id = '-1'
                } else {
                    app.source.show_children_id = id;

                }
            }

            app.submitReply = function (id) {


                if (app.source.ask_answer_obj.user_name.trim() == '') {
                    //layer.msg('{{__("LQuestionAnswer::common.rules_empty")}}');
                    return;
                }

                if (app.source.ask_answer_obj.user_name.trim().length < 2 || app.source.ask_answer_obj.user_name.trim().length > 30) {
                    //layer.msg('{{__("LQuestionAnswer::common.rules_limit",["min"=>2,"max"=>30])}}');
                    return;
                }

                if (app.source.ask_answer_obj.content_text.trim() == '') {
                    //layer.msg('{{__("LQuestionAnswer::common.rules_empty")}}');
                    return;
                }

                if (app.source.ask_answer_obj.content_text.trim().length < 10 || app.source.ask_answer_obj.content_text.trim().length > 350) {
                    //layer.msg('{{__("LQuestionAnswer::common.rules_limit",["min"=>10,"max"=>350])}}');
                    return;
                }

                axios.post('{{front_route("ask_answer.add")}}', app.source.ask_answer_obj).then((res) => {
                    layer.msg(res.message);
                    if (res.success) {
                        app.source.reply_id = '-1';
                        app.getAskAnswers();
                    }
                })

            }

            app.submitAgree = function (id, type) {

                axios.post('{{front_route("ask_answer.agree")}}', {type: type, id: id}).then((res) => {
                    layer.msg(res.message);
                    if (res.success) {
                        app.getAskAnswers();
                    }
                })
            }
        });


    </script>

    <style type="text/css" scoped>
        .comment {
            display: inline-block;
            vertical-align: top;
        }

        .comment-avatar {
            display: inline-block;
            vertical-align: top;
        }

        .emoj {
            /*width: 560px;*/
        }


        .tmsgBox {
            position: relative;
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .tmsg-respond h3 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .tmsg-respond h3 small {
            font-size: smaller;
            cursor: pointer;
        }

        .tmsg-respond textarea {
            background: #f4f6f7;
            height: 100px;
            margin-bottom: 10px;
        }

        .OwO {
            position: relative;
            z-index: 1;
        }

        .OwO .OwO-logo {
            position: relative;
            border-radius: 4px;
            color: #444;
            display: inline-block;
            background: #fff;
            border: 1px solid #ddd;
            font-size: 13px;
            padding: 0 6px;
            cursor: pointer;
            height: 30px;
            box-sizing: border-box;
            z-index: 2;
            line-height: 30px;
        }

        .OwO .OwO-logo:hover {
            animation: a 5s infinite ease-in-out;
            -webkit-animation: a 5s infinite ease-in-out;
        }

        .OwO .OwO-body {
            position: absolute;
            background: #fff;
            border: 1px solid #ddd;
            z-index: 1;
            top: 29px;
            border-radius: 0 4px 4px 4px;
            display: none;
        }

        .OwO-open .OwO-body {
            display: block;
        }

        .OwO-open .OwO-logo {
            border-radius: 4px 4px 0 0;
            border-bottom: none;
        }

        .OwO-open .OwO-logo:hover {
            animation: none;
            -webkit-animation: none;
        }

        .OwO .OwO-items {
            max-height: 197px;
            overflow: scroll;
            font-size: 0;
            padding: 10px;
            z-index: 1;
        }

        .OwO .OwO-items .OwO-item {
            background: #f7f7f7;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin: 0 10px 12px 0;
            transition: 0.3s;
            line-height: 19px;
            font-size: 20px;
            cursor: pointer;
        }

        .OwO .OwO-items .OwO-item:hover {
            background: #eee;
            box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.2),
            0 1px 5px 0 rgba(0, 0, 0, 0.12);
            animation: a 5s infinite ease-in-out;
            -webkit-animation: a 5s infinite ease-in-out;
        }

        .OwO .OwO-body .OwO-bar {
            width: 100%;
            height: 30px;
            border-top: 1px solid #ddd;
            background: #fff;
            border-radius: 0 0 4px 4px;
            color: #444;
        }

        .OwO .OwO-body .OwO-bar .OwO-packages li {
            display: inline-block;
            line-height: 30px;
            font-size: 14px;
            padding: 0 10px;
            cursor: pointer;
            margin-right: 3px;
            text-align: center;
        }

        .OwO .OwO-body .OwO-bar .OwO-packages li:first-of-type {
            border-radius: 0 0 0 3px;
        }

        @-webkit-keyframes a {
            2% {
                -webkit-transform: translateY(1.5px) rotate(1.5deg);
                transform: translateY(1.5px) rotate(1.5deg);
            }
            4% {
                -webkit-transform: translateY(-1.5px) rotate(-0.5deg);
                transform: translateY(-1.5px) rotate(-0.5deg);
            }
            6% {
                -webkit-transform: translateY(1.5px) rotate(-1.5deg);
                transform: translateY(1.5px) rotate(-1.5deg);
            }
            8% {
                -webkit-transform: translateY(-1.5px) rotate(-1.5deg);
                transform: translateY(-1.5px) rotate(-1.5deg);
            }
            10% {
                -webkit-transform: translateY(2.5px) rotate(1.5deg);
                transform: translateY(2.5px) rotate(1.5deg);
            }
            12% {
                -webkit-transform: translateY(-0.5px) rotate(1.5deg);
                transform: translateY(-0.5px) rotate(1.5deg);
            }
            14% {
                -webkit-transform: translateY(-1.5px) rotate(1.5deg);
                transform: translateY(-1.5px) rotate(1.5deg);
            }
            16% {
                -webkit-transform: translateY(-0.5px) rotate(-1.5deg);
                transform: translateY(-0.5px) rotate(-1.5deg);
            }
            18% {
                -webkit-transform: translateY(0.5px) rotate(-1.5deg);
                transform: translateY(0.5px) rotate(-1.5deg);
            }
            20% {
                -webkit-transform: translateY(-1.5px) rotate(2.5deg);
                transform: translateY(-1.5px) rotate(2.5deg);
            }
            22% {
                -webkit-transform: translateY(0.5px) rotate(-1.5deg);
                transform: translateY(0.5px) rotate(-1.5deg);
            }
            24% {
                -webkit-transform: translateY(1.5px) rotate(1.5deg);
                transform: translateY(1.5px) rotate(1.5deg);
            }
            26% {
                -webkit-transform: translateY(0.5px) rotate(0.5deg);
                transform: translateY(0.5px) rotate(0.5deg);
            }
            28% {
                -webkit-transform: translateY(0.5px) rotate(1.5deg);
                transform: translateY(0.5px) rotate(1.5deg);
            }
            30% {
                -webkit-transform: translateY(-0.5px) rotate(2.5deg);
                transform: translateY(-0.5px) rotate(2.5deg);
            }
            32%,
            34% {
                -webkit-transform: translateY(1.5px) rotate(-0.5deg);
                transform: translateY(1.5px) rotate(-0.5deg);
            }
            36% {
                -webkit-transform: translateY(-1.5px) rotate(2.5deg);
                transform: translateY(-1.5px) rotate(2.5deg);
            }
            38% {
                -webkit-transform: translateY(1.5px) rotate(-1.5deg);
                transform: translateY(1.5px) rotate(-1.5deg);
            }
            40% {
                -webkit-transform: translateY(-0.5px) rotate(2.5deg);
                transform: translateY(-0.5px) rotate(2.5deg);
            }
            42% {
                -webkit-transform: translateY(2.5px) rotate(-1.5deg);
                transform: translateY(2.5px) rotate(-1.5deg);
            }
            44% {
                -webkit-transform: translateY(1.5px) rotate(0.5deg);
                transform: translateY(1.5px) rotate(0.5deg);
            }
            46% {
                -webkit-transform: translateY(-1.5px) rotate(2.5deg);
                transform: translateY(-1.5px) rotate(2.5deg);
            }
            48% {
                -webkit-transform: translateY(-0.5px) rotate(0.5deg);
                transform: translateY(-0.5px) rotate(0.5deg);
            }
            50% {
                -webkit-transform: translateY(0.5px) rotate(0.5deg);
                transform: translateY(0.5px) rotate(0.5deg);
            }
            52% {
                -webkit-transform: translateY(2.5px) rotate(2.5deg);
                transform: translateY(2.5px) rotate(2.5deg);
            }
            54% {
                -webkit-transform: translateY(-1.5px) rotate(1.5deg);
                transform: translateY(-1.5px) rotate(1.5deg);
            }
            56% {
                -webkit-transform: translateY(2.5px) rotate(2.5deg);
                transform: translateY(2.5px) rotate(2.5deg);
            }
            58% {
                -webkit-transform: translateY(0.5px) rotate(2.5deg);
                transform: translateY(0.5px) rotate(2.5deg);
            }
            60% {
                -webkit-transform: translateY(2.5px) rotate(2.5deg);
                transform: translateY(2.5px) rotate(2.5deg);
            }
            62% {
                -webkit-transform: translateY(-0.5px) rotate(2.5deg);
                transform: translateY(-0.5px) rotate(2.5deg);
            }
            64% {
                -webkit-transform: translateY(-0.5px) rotate(1.5deg);
                transform: translateY(-0.5px) rotate(1.5deg);
            }
            66% {
                -webkit-transform: translateY(1.5px) rotate(-0.5deg);
                transform: translateY(1.5px) rotate(-0.5deg);
            }
            68% {
                -webkit-transform: translateY(-1.5px) rotate(-0.5deg);
                transform: translateY(-1.5px) rotate(-0.5deg);
            }
            70% {
                -webkit-transform: translateY(1.5px) rotate(0.5deg);
                transform: translateY(1.5px) rotate(0.5deg);
            }
            72% {
                -webkit-transform: translateY(2.5px) rotate(1.5deg);
                transform: translateY(2.5px) rotate(1.5deg);
            }
            74% {
                -webkit-transform: translateY(-0.5px) rotate(0.5deg);
                transform: translateY(-0.5px) rotate(0.5deg);
            }
            76% {
                -webkit-transform: translateY(-0.5px) rotate(2.5deg);
                transform: translateY(-0.5px) rotate(2.5deg);
            }
            78% {
                -webkit-transform: translateY(-0.5px) rotate(1.5deg);
                transform: translateY(-0.5px) rotate(1.5deg);
            }
            80% {
                -webkit-transform: translateY(1.5px) rotate(1.5deg);
                transform: translateY(1.5px) rotate(1.5deg);
            }
            82% {
                -webkit-transform: translateY(-0.5px) rotate(0.5deg);
                transform: translateY(-0.5px) rotate(0.5deg);
            }
            84% {
                -webkit-transform: translateY(1.5px) rotate(2.5deg);
                transform: translateY(1.5px) rotate(2.5deg);
            }
            86% {
                -webkit-transform: translateY(-1.5px) rotate(-1.5deg);
                transform: translateY(-1.5px) rotate(-1.5deg);
            }
            88% {
                -webkit-transform: translateY(-0.5px) rotate(2.5deg);
                transform: translateY(-0.5px) rotate(2.5deg);
            }
            90% {
                -webkit-transform: translateY(2.5px) rotate(-0.5deg);
                transform: translateY(2.5px) rotate(-0.5deg);
            }
            92% {
                -webkit-transform: translateY(0.5px) rotate(-0.5deg);
                transform: translateY(0.5px) rotate(-0.5deg);
            }
            94% {
                -webkit-transform: translateY(2.5px) rotate(0.5deg);
                transform: translateY(2.5px) rotate(0.5deg);
            }
            96% {
                -webkit-transform: translateY(-0.5px) rotate(1.5deg);
                transform: translateY(-0.5px) rotate(1.5deg);
            }
            98% {
                -webkit-transform: translateY(-1.5px) rotate(-0.5deg);
                transform: translateY(-1.5px) rotate(-0.5deg);
            }
            0%,
            to {
                -webkit-transform: translate(0) rotate(0deg);
                transform: translate(0) rotate(0deg);
            }
        }

        @keyframes a {
            2% {
                -webkit-transform: translateY(1.5px) rotate(1.5deg);
                transform: translateY(1.5px) rotate(1.5deg);
            }
            4% {
                -webkit-transform: translateY(-1.5px) rotate(-0.5deg);
                transform: translateY(-1.5px) rotate(-0.5deg);
            }
            6% {
                -webkit-transform: translateY(1.5px) rotate(-1.5deg);
                transform: translateY(1.5px) rotate(-1.5deg);
            }
            8% {
                -webkit-transform: translateY(-1.5px) rotate(-1.5deg);
                transform: translateY(-1.5px) rotate(-1.5deg);
            }
            10% {
                -webkit-transform: translateY(2.5px) rotate(1.5deg);
                transform: translateY(2.5px) rotate(1.5deg);
            }
            12% {
                -webkit-transform: translateY(-0.5px) rotate(1.5deg);
                transform: translateY(-0.5px) rotate(1.5deg);
            }
            14% {
                -webkit-transform: translateY(-1.5px) rotate(1.5deg);
                transform: translateY(-1.5px) rotate(1.5deg);
            }
            16% {
                -webkit-transform: translateY(-0.5px) rotate(-1.5deg);
                transform: translateY(-0.5px) rotate(-1.5deg);
            }
            18% {
                -webkit-transform: translateY(0.5px) rotate(-1.5deg);
                transform: translateY(0.5px) rotate(-1.5deg);
            }
            20% {
                -webkit-transform: translateY(-1.5px) rotate(2.5deg);
                transform: translateY(-1.5px) rotate(2.5deg);
            }
            22% {
                -webkit-transform: translateY(0.5px) rotate(-1.5deg);
                transform: translateY(0.5px) rotate(-1.5deg);
            }
            24% {
                -webkit-transform: translateY(1.5px) rotate(1.5deg);
                transform: translateY(1.5px) rotate(1.5deg);
            }
            26% {
                -webkit-transform: translateY(0.5px) rotate(0.5deg);
                transform: translateY(0.5px) rotate(0.5deg);
            }
            28% {
                -webkit-transform: translateY(0.5px) rotate(1.5deg);
                transform: translateY(0.5px) rotate(1.5deg);
            }
            30% {
                -webkit-transform: translateY(-0.5px) rotate(2.5deg);
                transform: translateY(-0.5px) rotate(2.5deg);
            }
            32%,
            34% {
                -webkit-transform: translateY(1.5px) rotate(-0.5deg);
                transform: translateY(1.5px) rotate(-0.5deg);
            }
            36% {
                -webkit-transform: translateY(-1.5px) rotate(2.5deg);
                transform: translateY(-1.5px) rotate(2.5deg);
            }
            38% {
                -webkit-transform: translateY(1.5px) rotate(-1.5deg);
                transform: translateY(1.5px) rotate(-1.5deg);
            }
            40% {
                -webkit-transform: translateY(-0.5px) rotate(2.5deg);
                transform: translateY(-0.5px) rotate(2.5deg);
            }
            42% {
                -webkit-transform: translateY(2.5px) rotate(-1.5deg);
                transform: translateY(2.5px) rotate(-1.5deg);
            }
            44% {
                -webkit-transform: translateY(1.5px) rotate(0.5deg);
                transform: translateY(1.5px) rotate(0.5deg);
            }
            46% {
                -webkit-transform: translateY(-1.5px) rotate(2.5deg);
                transform: translateY(-1.5px) rotate(2.5deg);
            }
            48% {
                -webkit-transform: translateY(-0.5px) rotate(0.5deg);
                transform: translateY(-0.5px) rotate(0.5deg);
            }
            50% {
                -webkit-transform: translateY(0.5px) rotate(0.5deg);
                transform: translateY(0.5px) rotate(0.5deg);
            }
            52% {
                -webkit-transform: translateY(2.5px) rotate(2.5deg);
                transform: translateY(2.5px) rotate(2.5deg);
            }
            54% {
                -webkit-transform: translateY(-1.5px) rotate(1.5deg);
                transform: translateY(-1.5px) rotate(1.5deg);
            }
            56% {
                -webkit-transform: translateY(2.5px) rotate(2.5deg);
                transform: translateY(2.5px) rotate(2.5deg);
            }
            58% {
                -webkit-transform: translateY(0.5px) rotate(2.5deg);
                transform: translateY(0.5px) rotate(2.5deg);
            }
            60% {
                -webkit-transform: translateY(2.5px) rotate(2.5deg);
                transform: translateY(2.5px) rotate(2.5deg);
            }
            62% {
                -webkit-transform: translateY(-0.5px) rotate(2.5deg);
                transform: translateY(-0.5px) rotate(2.5deg);
            }
            64% {
                -webkit-transform: translateY(-0.5px) rotate(1.5deg);
                transform: translateY(-0.5px) rotate(1.5deg);
            }
            66% {
                -webkit-transform: translateY(1.5px) rotate(-0.5deg);
                transform: translateY(1.5px) rotate(-0.5deg);
            }
            68% {
                -webkit-transform: translateY(-1.5px) rotate(-0.5deg);
                transform: translateY(-1.5px) rotate(-0.5deg);
            }
            70% {
                -webkit-transform: translateY(1.5px) rotate(0.5deg);
                transform: translateY(1.5px) rotate(0.5deg);
            }
            72% {
                -webkit-transform: translateY(2.5px) rotate(1.5deg);
                transform: translateY(2.5px) rotate(1.5deg);
            }
            74% {
                -webkit-transform: translateY(-0.5px) rotate(0.5deg);
                transform: translateY(-0.5px) rotate(0.5deg);
            }
            76% {
                -webkit-transform: translateY(-0.5px) rotate(2.5deg);
                transform: translateY(-0.5px) rotate(2.5deg);
            }
            78% {
                -webkit-transform: translateY(-0.5px) rotate(1.5deg);
                transform: translateY(-0.5px) rotate(1.5deg);
            }
            80% {
                -webkit-transform: translateY(1.5px) rotate(1.5deg);
                transform: translateY(1.5px) rotate(1.5deg);
            }
            82% {
                -webkit-transform: translateY(-0.5px) rotate(0.5deg);
                transform: translateY(-0.5px) rotate(0.5deg);
            }
            84% {
                -webkit-transform: translateY(1.5px) rotate(2.5deg);
                transform: translateY(1.5px) rotate(2.5deg);
            }
            86% {
                -webkit-transform: translateY(-1.5px) rotate(-1.5deg);
                transform: translateY(-1.5px) rotate(-1.5deg);
            }
            88% {
                -webkit-transform: translateY(-0.5px) rotate(2.5deg);
                transform: translateY(-0.5px) rotate(2.5deg);
            }
            90% {
                -webkit-transform: translateY(2.5px) rotate(-0.5deg);
                transform: translateY(2.5px) rotate(-0.5deg);
            }
            92% {
                -webkit-transform: translateY(0.5px) rotate(-0.5deg);
                transform: translateY(0.5px) rotate(-0.5deg);
            }
            94% {
                -webkit-transform: translateY(2.5px) rotate(0.5deg);
                transform: translateY(2.5px) rotate(0.5deg);
            }
            96% {
                -webkit-transform: translateY(-0.5px) rotate(1.5deg);
                transform: translateY(-0.5px) rotate(1.5deg);
            }
            98% {
                -webkit-transform: translateY(-1.5px) rotate(-0.5deg);
                transform: translateY(-1.5px) rotate(-0.5deg);
            }
            0%,
            to {
                -webkit-transform: translate(0) rotate(0deg);
                transform: translate(0) rotate(0deg);
            }
        }

        /*用户输入表单*/
        .tmsg-r-info {
            margin: 10px 0;
        }

        .tmsg-r-info input {
            height: 30px;
            border-radius: 4px;
            background: #f4f6f7;
        }

        .tmsg-r-info .info-submit {
            margin: 10px 0;
            text-align: center;
        }

        .tmsg-r-info .info-submit p,
        .tmsg-commentshow h1 {
            /*background: #97dffd;*/
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            /*transition: all .3s ease-in-out;*/
            height: 30px;
            line-height: 30px;
            text-align: center;
        }

        /*.tmsg-r-info .info-submit p:hover{
            background: #47456d;
        }*/
        /*评论列表*/
        .tmsg-comments .tmsg-comments-tip {
            display: block;
            border-left: 2px solid #363d4c;
            padding: 0 10px;
            margin: 40px 0;
            font-size: 20px;
        }

        .tmsg-commentlist {
            margin-bottom: 20px;
        }

        .tmsg-commentshow > .tmsg-commentlist {
            border-bottom: 1px solid #e5eaed;
        }

        .tmsg-c-item {
            border-top: 1px solid #e5eaed;
        }

        .tmsg-c-item article {
            margin: 20px 0;
        }

        .tmsg-c-item article header {
            margin-bottom: 10px;
        }

        .tmsg-c-item article header img {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            float: left;
            transition: all 0.4s ease-in-out;
            -webkit-transition: all 0.4s ease-in-out;
            margin-right: 15px;
            object-fit: cover;
        }

        .tmsg-c-item article header img:hover {
            transform: rotate(360deg);
            -webkit-transform: rotate(360deg);
        }

        .tmsg-c-item article header .i-name {
            font-size: 14px;
            margin: 5px 8px 7px 0;
            color: #444;
            font-weight: bold;
            display: inline-block;
        }

        .tmsg-c-item article header .i-class {
            display: inline-block;
            margin-left: 10px;
            background: #dff0d8;
            color: #3c763d;
            border-radius: 5px;
            padding: 3px 6px;
            font-size: 12px;
            font-weight: 400;
        }

        .tmsg-c-item article header .i-time {
            color: #aaa;
            font-size: 12px;
        }

        .tmsg-c-item article section {
            margin-left: 80px;
        }

        .tmsg-c-item article section p img {
            vertical-align: middle;
        }

        .tmsg-c-item article section .tmsg-replay {
            margin: 10px 0;
            font-size: 12px;
            color: #64609e;
            cursor: pointer;
        }

        .hbl-owo {
            text-align: left;
        }

        .comm {
            padding: 20px;
        }

        .su {
            margin-top: 2px;
            width: 5px;
            height: 23px;
            background: #3cb371; /*#1E90FF*/
        }

        .com-rep {
            display: inline-block;
            vertical-align: top;
        }

        .com-title {
            font-size: 20px;
            margin-left: 5px;
        }

        .com-span {
            font-size: 16px;
        }

        .hbl-fa {
            text-align: left;
        }

        .hbl-comm {
            padding: 40px;
        }

        .reply {
            border-top: solid 1px #d9d9d9;
        }

        .content {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .comment-f {
            display: inline-block;
            vertical-align: top;
        }

        .nickname {
            font-size: 14px;
        }

        .author {
            display: inline-block;
        }

        .icon {
            background: #dff0d8;
            color: #3c763d;
            border-radius: 5px;
            padding: 3px 6px;
            font-size: 12px;
            font-weight: 400px;
        }

        .date {
            font-size: 12px;
            margin-top: 5px;
            color: grey;
        }

        .reply-content {
            word-wrap: break-word;
            width: 90%;
            font-size: 15px;
            line-height: 25px;
            margin-left: 56px;
        }

        .reply-fa {
            margin-top: 5px;
        }

        .reply-font {
            margin-bottom: 5px;
            color: grey;
            cursor: pointer;
        }

        .children {
            padding-left: 40px;
        }

        .cc {
            display: inline-block;
        }

        .cc-to a {
            text-decoration: none;
            color: #409eff;
        }

        .icon-reply {
            display: inline-block;
            vertical-align: top;
        }

        .icon-hf {
            margin-top: 2px;
        }

        .hbl-child {
            padding: 20px;
        }

        .publish-btn {
            margin-top: 20px;
        }
    </style>

@endpush
