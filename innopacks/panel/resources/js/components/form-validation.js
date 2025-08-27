/**
 * Form Validation module.
 *
 * This module handles Bootstrap form validation functionality.
 */
const FormValidation = {
  /**
   * 初始化表单验证
   */
  init() {
    // 处理需要验证的表单
    $(document).on('submit', '.needs-validation', function(event) {
      const form = this;
      
      if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
        
        // 滚动到第一个错误字段
        FormValidation.scrollToFirstError(form);
        
        // 高亮显示包含错误的选项卡
        FormValidation.highlightErrorTabs(form);
      }
      
      form.classList.add('was-validated');
    });

    // 实时验证字段
    $(document).on('blur change', '.needs-validation input, .needs-validation select, .needs-validation textarea', function() {
      const field = this;
      const form = field.closest('.needs-validation');
      
      if (form) {
        // 验证单个字段
        if (field.checkValidity()) {
          field.classList.remove('is-invalid');
          field.classList.add('is-valid');
        } else {
          field.classList.remove('is-valid');
          field.classList.add('is-invalid');
        }
      }
    });

    // 处理自定义验证消息
    $(document).on('invalid', '.needs-validation input, .needs-validation select, .needs-validation textarea', function() {
      const field = this;
      const customMessage = field.getAttribute('data-validation-message');
      
      if (customMessage) {
        field.setCustomValidity(customMessage);
      }
    });

    // 清除自定义验证消息
    $(document).on('input change', '.needs-validation input, .needs-validation select, .needs-validation textarea', function() {
      this.setCustomValidity('');
    });
  },

  /**
   * 滚动到第一个错误字段
   * @param {HTMLElement} form - 表单元素
   */
  scrollToFirstError(form) {
    const firstInvalidField = form.querySelector(':invalid');
    if (firstInvalidField) {
      firstInvalidField.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
      
      // 聚焦到错误字段
      setTimeout(() => {
        firstInvalidField.focus();
      }, 300);
    }
  },

  /**
   * 高亮显示包含错误的选项卡
   * @param {HTMLElement} form - 表单元素
   */
  highlightErrorTabs(form) {
    // 清除之前的错误标记
    $(form).find('.nav-link').removeClass('text-danger');
    
    // 查找包含错误字段的选项卡面板
    $(form).find('.tab-pane').each(function() {
      const tabPane = this;
      const invalidFields = tabPane.querySelectorAll(':invalid');
      
      if (invalidFields.length > 0) {
        // 找到对应的选项卡链接
        const tabId = tabPane.id;
        const tabLink = document.querySelector(`[href="#${tabId}"], [data-bs-target="#${tabId}"]`);
        
        if (tabLink) {
          tabLink.classList.add('text-danger');
          
          // 添加错误图标
          if (!tabLink.querySelector('.error-icon')) {
            const errorIcon = document.createElement('i');
            errorIcon.className = 'bi bi-exclamation-triangle-fill ms-1 error-icon';
            tabLink.appendChild(errorIcon);
          }
        }
      }
    });
  },

  /**
   * 手动验证表单
   * @param {string|HTMLElement} formSelector - 表单选择器或表单元素
   * @returns {boolean} 验证结果
   */
  validateForm(formSelector) {
    const form = typeof formSelector === 'string' ? document.querySelector(formSelector) : formSelector;
    
    if (!form) {
      return false;
    }
    
    const isValid = form.checkValidity();
    form.classList.add('was-validated');
    
    if (!isValid) {
      this.scrollToFirstError(form);
      this.highlightErrorTabs(form);
    }
    
    return isValid;
  },

  /**
   * 重置表单验证状态
   * @param {string|HTMLElement} formSelector - 表单选择器或表单元素
   */
  resetValidation(formSelector) {
    const form = typeof formSelector === 'string' ? document.querySelector(formSelector) : formSelector;
    
    if (!form) {
      return;
    }
    
    form.classList.remove('was-validated');
    
    // 清除字段验证状态
    $(form).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    
    // 清除选项卡错误标记
    $(form).find('.nav-link').removeClass('text-danger');
    $(form).find('.error-icon').remove();
  }
};

export default FormValidation;