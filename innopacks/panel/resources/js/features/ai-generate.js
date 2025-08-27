import Api from "../core/api";

/**
 * AI Generate feature.
 *
 * Handles the AI content generation functionality.
 */
const AIGenerate = {
  /**
   * Initializes the event listener for the AI generate button.
   */
  init: () => {
    $(document).on("click", ".ai-generate", function (e) {
      // Find the form-row containing the current button
      const $row = $(this).closest(".form-row");
      // Find input or textarea within the row
      const $input = $row.find("input[data-column], textarea[data-column]");
      if ($input.length === 0) {
        layer.msg('Input field not found', { icon: 2 });
        return;
      }
      // Get field name, language, and current value
      const column = $input.data('column');
      const lang = $input.data('lang');
      const name = $input.attr('name');
      const value = $input.val();

      // Assemble request data
      const formData = {
        column: column,
        lang: lang,
        name: name,
        value: value,
        // Add product_id and other parameters as needed
      };

      Api.post(urls.ai_generate, formData)
        .then(function (res) {
          if (res.data && res.data.generated_text) {
            $input.val(res.data.generated_text);
          } else if (res.data && res.data.message) {
            $input.val(res.data.message);
          }
        });
    });
  }
};

export default AIGenerate;