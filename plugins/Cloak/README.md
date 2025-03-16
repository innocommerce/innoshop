# Cloak Plugin for InnoShop

This plugin provides cloaking functionality for your InnoShop platform, allowing you to show different content to different visitors based on various criteria such as IP address, user agent, location, and more.

## Features

- Show different content to visitors based on their characteristics
- Automatically detect and filter bots and ad network crawlers
- One-time redirect option (show target URL only on first visit)
- Filter by IP address, country, user agent, and referrer
- UTM parameter filtering
- Detailed statistics on visits and redirects
- Simple administration interface

## Use Cases

1. **Ad Compliance**: Show a compliant version of your page to ad network reviewers, and your actual marketing page to real visitors.
2. **Regional Content**: Display different content to visitors from different countries.
3. **Bot Protection**: Hide sensitive content from bots and crawlers.
4. **A/B Testing**: Show different landing pages to different segments of visitors.

## Installation

1. Upload the plugin to your InnoShop plugins directory.
2. Activate the plugin from the InnoShop admin panel.
3. Configure your cloaking rules under the "Cloak" section in the admin panel.

## How to Use

1. **Create a New Cloak**:
   - Go to the admin panel and navigate to the "Cloak" section.
   - Click "Add Cloak" to create a new cloaking rule.
   - Enter a name and optional description for your cloak.

2. **Configure URLs**:
   - Set the target URL (money page) - the page you want real visitors to see.
   - Set the safe URL (white page) - the page you want bots and reviewers to see.
   - Enable/disable the cloak with the "Is Active" toggle.

3. **Set Up Filtering Options**:
   - Enable "Auto-detect Bots" to automatically filter known bot user agents.
   - Enable "One-time Redirect" if you want visitors to see the target URL only on their first visit.
   - Add IP filters (IP addresses or CIDR ranges) to filter specific IPs.
   - Add country filters (country codes like US, CA) to filter by location.
   - Add user agent filters to target specific browsers or devices.
   - Add referrer filters to filter based on where visitors are coming from.

4. **Configure UTM Parameters** (optional):
   - Set UTM source, medium, and campaign parameters for tracking.
   - Visitors with matching UTM parameters will see the target URL, others will see the safe URL.

5. **Monitor Performance**:
   - Track visits (total number of visitors to your cloak URL).
   - Track redirects (number of visitors who saw the target URL).
   - Use these metrics to analyze the effectiveness of your cloaking strategy.

6. **Test Your Cloak**:
   - Use the test button in the admin panel to verify your cloak works as expected.
   - Try accessing from different devices, locations, or through VPNs to test filtering rules.

## Technical Support

If you encounter any issues or have questions about this plugin, please contact the author:

Telegram: InnoShop Global

Author: XING GUI YU (Sam Xing)  
Email: xingguiyu@foxmail.com  
WeChat: crystalparfait  
QQ: 1061061061

InnoShop Discussion Group: 799563037  
InnoCMS Discussion Group: 960062283

The author also offers custom plugin development, feature customization, and theme customization services for InnoShop. Contact the author for more details.

## License

Copyright (c) Since 2024 InnoShop - All Rights Reserved.  
Licensed under the Open Software License (OSL 3.0).

----

# 斗篷插件使用说明

本插件为InnoShop平台提供强大的访问筛选（斗篷）功能，让您能够根据访问者的IP地址、用户代理、地理位置等特征向不同访问者展示不同内容。

## 功能特点

- 根据访问者特征显示不同内容
- 自动检测和筛选机器人及广告网络爬虫
- 一次性重定向选项（仅在首次访问时显示目标URL）
- 按IP地址、国家、用户代理和来源网址进行筛选
- UTM参数筛选
- 详细的访问和重定向统计
- 简洁的管理界面

## 应用场景

1. **广告合规**: 向广告网络审核人员展示合规版页面，向真实访问者展示实际营销页面。
2. **区域内容**: 向不同国家的访问者展示不同内容。
3. **机器人保护**: 对机器人和爬虫隐藏敏感内容。
4. **A/B测试**: 向不同细分访问者显示不同着陆页。

## 使用方法

1. **创建新斗篷**:
   - 进入管理面板并导航至"斗篷"部分。
   - 点击"添加斗篷"创建新的筛选规则。
   - 输入斗篷名称和可选描述。

2. **配置URL**:
   - 设置目标URL（营销页面）- 您希望真实访问者看到的页面。
   - 设置安全URL（白页）- 您希望机器人和审核者看到的页面。
   - 使用"是否启用"开关启用/禁用斗篷。

3. **设置筛选选项**:
   - 启用"自动检测机器人"以自动筛选已知机器人用户代理。
   - 如果您希望访问者仅在首次访问时看到目标URL，请启用"一次性重定向"。
   - 添加IP筛选器（IP地址或CIDR范围）以筛选特定IP。
   - 添加国家筛选器（国家代码如US、CA）以按位置筛选。
   - 添加用户代理筛选器以针对特定浏览器或设备。
   - 添加来源网址筛选器以根据访问者来源进行筛选。

4. **配置UTM参数**（可选）:
   - 设置UTM来源、媒介和活动参数用于跟踪。
   - 具有匹配UTM参数的访问者将看到目标URL，其他人将看到安全URL。

5. **监控性能**:
   - 跟踪访问次数（访问您的斗篷URL的总访问者数）。
   - 跟踪重定向次数（看到目标URL的访问者数量）。
   - 使用这些指标分析您的筛选策略的有效性。

6. **测试您的斗篷**:
   - 使用管理面板中的测试按钮验证您的斗篷是否按预期工作。
   - 尝试从不同设备、位置或通过VPN访问以测试筛选规则。

## 技术支持

如果您在使用过程中遇到任何问题或有任何疑问，请联系作者：

Telegram: InnoShop Global

作者：XING GUI YU（Sam Xing）  
邮箱：xingguiyu@foxmail.com  
微信：crystalparfait  
QQ：1061061061

InnoShop交流群：799563037  
InnoCMS交流群：960062283

作者还提供InnoShop平台的插件定制、功能定制和主题定制服务，有需要请联系作者。

## 许可证

版权所有 (c) Since 2024 InnoShop - 保留所有权利。  
基于开放软件许可证 (OSL 3.0) 授权。

----

# 斗篷插件使用說明

本插件為InnoShop平台提供強大的訪問篩選（斗篷）功能，讓您能夠根據訪問者的IP地址、用戶代理、地理位置等特徵向不同訪問者展示不同內容。

## 功能特點

- 根據訪問者特徵顯示不同內容
- 自動檢測和篩選機器人及廣告網絡爬蟲
- 一次性重定向選項（僅在首次訪問時顯示目標URL）
- 按IP地址、國家、用戶代理和來源網址進行篩選
- UTM參數篩選
- 詳細的訪問和重定向統計
- 簡潔的管理界面

## 應用場景

1. **廣告合規**: 向廣告網絡審核人員展示合規版頁面，向真實訪問者展示實際營銷頁面。
2. **區域內容**: 向不同國家的訪問者展示不同內容。
3. **機器人保護**: 對機器人和爬蟲隱藏敏感內容。
4. **A/B測試**: 向不同細分訪問者顯示不同著陸頁。

## 使用方法

1. **創建新斗篷**:
   - 進入管理面板並導航至"斗篷"部分。
   - 點擊"添加斗篷"創建新的篩選規則。
   - 輸入斗篷名稱和可選描述。

2. **配置URL**:
   - 設置目標URL（營銷頁面）- 您希望真實訪問者看到的頁面。
   - 設置安全URL（白頁）- 您希望機器人和審核者看到的頁面。
   - 使用"是否啟用"開關啟用/禁用斗篷。

3. **設置篩選選項**:
   - 啟用"自動檢測機器人"以自動篩選已知機器人用戶代理。
   - 如果您希望訪問者僅在首次訪問時看到目標URL，請啟用"一次性重定向"。
   - 添加IP篩選器（IP地址或CIDR範圍）以篩選特定IP。
   - 添加國家篩選器（國家代碼如US、CA）以按位置篩選。
   - 添加用戶代理篩選器以針對特定瀏覽器或設備。
   - 添加來源網址篩選器以根據訪問者來源進行篩選。

4. **配置UTM參數**（可選）:
   - 設置UTM來源、媒介和活動參數用於跟蹤。
   - 具有匹配UTM參數的訪問者將看到目標URL，其他人將看到安全URL。

5. **監控性能**:
   - 跟蹤訪問次數（訪問您的斗篷URL的總訪問者數）。
   - 跟蹤重定向次數（看到目標URL的訪問者數量）。
   - 使用這些指標分析您的篩選策略的有效性。

6. **測試您的斗篷**:
   - 使用管理面板中的測試按鈕驗證您的斗篷是否按預期工作。
   - 嘗試從不同設備、位置或通過VPN訪問以測試篩選規則。

## 技術支持

如果您在使用過程中遇到任何問題或有任何疑問，請聯繫作者：

Telegram: InnoShop Global

作者：XING GUI YU（Sam Xing）  
郵箱：xingguiyu@foxmail.com  
微信：crystalparfait  
QQ：1061061061

InnoShop交流群：799563037  
InnoCMS交流群：960062283

作者還提供InnoShop平台的插件定制、功能定制和主題定制服務，有需要請聯繫作者。

## 許可證

版權所有 (c) Since 2024 InnoShop - 保留所有權利。  
基於開放軟件許可證 (OSL 3.0) 授權。 
