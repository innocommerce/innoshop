@extends('panel::layouts.app')
@section('title', $name)
<script src="{{ plugin_asset('inno_chat', 'js/marked/marked.min.js') }}"></script>
@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      <div class="col-12 answer-wrap">
        <div class="p-3 bg-white chat-container" id="answer">
          <div class="chat-detail" id="chat-detail">
            <div class="not-answer " id="not-answer">
              <i class="bi bi-activity"></i> {{ __('InnoChat::common.no_question') }}
            </div>
          </div>
        </div>
        <div class="input-group mb-3 mt-4 d-flex justify-content-center">
          <form class="no-load d-flex ">
            <div>
              <input type="text" id="ai-input" class="form-control  custom-rounded" name="input"
                     placeholder="{{ __('InnoChat::common.enter_question') }}"
                     aria-label="{{ __('InnoChat::common.enter_question') }}" aria-describedby="button-addon2">
            </div>
            <div>
              <button class="btn btn-primary px-4 btn-heigh" type="submit" id="ai-submit"><i
                    class="bi bi-send-fill"></i>
                {{ __('panel/common.confirm') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    const form = document.querySelector('form');
    const answerContainer = document.getElementById("chat-detail");
    const notanswer = document.getElementById("not-answer");

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const input = event.target.input.value
      if (input === "") return;
      notanswer.style.display = "none";

      const userMessageDiv = document.createElement("div");
      userMessageDiv.className = "chat-right"; // 设置样式类
      userMessageDiv.innerHTML = `
        <p class="bot-response">${input}</p>
        <i class="bi bi-person-fill icon-full-height"></i>
    `;
      answerContainer.appendChild(userMessageDiv); // 添加到容器中
      event.target.input.value = "";

      const source = new EventSource('/panel/inno_chat/completions?question=' + encodeURIComponent(input));
      // 监听服务器返回的数据
      let botMessageDiv = null; // 用于存储机器人消息的 div
      marked.setOptions({
        breaks: false, // 禁用自动换行
        highlight: function (code, lang) {
          if (lang && hljs.getLanguage(lang)) {
            return hljs.highlight(lang, code, true).value;
          } else {
            return hljs.highlightAuto(code).value;
          }
        }
      });

      source.addEventListener('update', function (event) {
        if (event.data === "<END_STREAMING_SSE>") {
          source.close();
          return;
        }

        // 如果是第一次接收到数据，创建机器人消息的 div
        if (!botMessageDiv) {
          botMessageDiv = document.createElement("div");
          botMessageDiv.className = "chat-left"; // 设置样式类
          botMessageDiv.innerHTML = `
                <i class="bi bi-robot icon-full-height"></i>
                <p class="user-question"></p>
            `;
          answerContainer.appendChild(botMessageDiv); // 添加到容器中
        }

        // 合并 Markdown 内容
        let answer = marked.parse(event.data);
        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = answer;
        let plainText = "";
        const paragraphs = tempDiv.querySelectorAll("p");
        paragraphs.forEach(p => {
          plainText += p.innerText + " ";
        });

        // 去除多余的空格
        plainText = plainText.trim();
        // 追加机器人回复内容
        const botResponseText = botMessageDiv.querySelector(".user-question");
        botResponseText.innerText += plainText
        // 自动滚动到底部

        answerContainer.scrollTop = answerContainer.scrollHeight;
      });
    });
  </script>

  <style>
    /* 外层容器 */
    #answer {
      /* 允许垂直滚动 */
      border: 1px solid #dddddd;
      /* 边框 */
      border-radius: 10px;
      /* 圆角 */
      padding: 15px;
      /* 内边距 */
      background: #007bff;
    }

    .chat-detail {
      height: 400px;
      gap: 15px;
      /* 固定高度 */
      overflow-y: auto;
      /* 允许垂直滚动 */
      border: 1px solid #ddd;
      /* 边框 */
      border-radius: 10px;
      /* 圆角 */
      padding: 15px;
      /* 内边距 */
      background-color: #f9f9f9;
    }

    .chat-container {
      max-width: 800px;
      margin: auto;
      padding: 15px;
    }

    .placeholder {
      display: none;
    }

    .chat-left {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-right: auto;
      /* 靠左对齐 */
      max-width: 80%;
      /* 最大宽度 */
    }

    .chat-right {
      display: flex;
      justify-content: end;
      gap: 10px;
      margin-left: auto;
    }

    .user-question {
      background-color: #d1d1d1;
      color: #333;
      padding: 10px 15px;
      border-radius: 10px 10px 10px 0;
      align-items: stretch;
      max-width: 700px;
      gap: 10px;
    }

    .bot-response {
      background-color: #007bff;
      color: white;
      padding: 10px 15px;
      border-radius: 10px 10px 0 10px;
      display: inline;
      max-width: 700px;
      align-items: stretch;
      gap: 10px;
    }

    .icon-full-height {
      display: flex;
      /* 启用 flexbox 布局 */
      align-items: center;
      /* 垂直居中 */
      justify-content: center;
      font-size: 2rem;
      /* 白色图标 */
      padding: 0 10px;
    }

    .btn-heigh {
      height: 100%;
      border-radius: 0 10px 10px 0;
    }

    .custom-rounded {
      border-radius: 10px 0 0 10px;
      width: 20rem;
      /* 左上角和左下角圆角，右上角和右下角直角 */
    }

    .not-answer {
      text-align: center;
      line-height: 400px;
      font-size: 20px;
      color: #999;
    }

    .answer-list {
      white-space: pre-wrap;
    }

    .answer-list p {
      margin-bottom: 0;
    }

    .created-at {
      text-align: center;
      color: #999;
      font-size: 12px;
      margin: 30px 0;
      position: relative;
    }

    .created-at span {
      background-color: #fff;
      padding: 0 10px;
      position: relative;
    }

    .created-at:before {
      content: '';
      display: inline-block;
      width: 100%;
      height: 1px;
      background-color: #eee;
      position: absolute;
      top: 50%;
      left: 0;
    }

    .answer-wrap pre {
      display: block;
      background-color: #f3f3f3;
      padding: .5rem !important;
      overflow-y: auto;
      font-weight: 300;
      font-family: Menlo, monospace;
      border-radius: .3rem;
      margin-bottom: 0;
    }

    .answer-wrap pre {
      background-color: #283646 !important;
    }

    .answer-wrap pre > code {
      border: 0px !important;
      background-color: #283646 !important;
      color: #FFF;
    }

    .answer-wrap ol,
    .answer-wrap ul,
    .answer-wrap dl {
      margin-bottom: 0;
      padding-left: 14px;
      line-height: 1;
    }
  </style>
@endsection
