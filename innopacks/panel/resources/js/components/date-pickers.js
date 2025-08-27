/**
 * Date pickers component.
 *
 * Initializes date, datetime, and time pickers using laydate.
 */
const DatePickers = {
  /**
   * Initializes the event listener for date picker inputs.
   */
  init: () => {
    $(document).on("focus", ".date input, .datetime input, .time input", function (event) {
      if (!$(this).prop("id")) {
        $(this).prop("id", Math.random().toString(36).substring(2));
      }

      $(this).attr("autocomplete", "off");

      laydate.render({
        elem: "#" + $(this).prop("id"),
        type: $(this).parent().hasClass("date")
          ? "date"
          : $(this).parent().hasClass("datetime")
          ? "datetime"
          : "time",
        trigger: "click",
        lang: $("html").prop("lang") == "zh-cn" ? "cn" : "en",
      });
    });
  },
};

export default DatePickers;