<x-admin-form-select name="google_feed_status" id="google_feed_status" title="GoogleFeed 状态"
                     :value="old('google_feed_status', $product->google_feed_status ?? 1)"
                     :options="[['title' =>'关闭', 'id' => 0],['title' =>'开启', 'id' => 1]]"
                     key="id" label="title"/>
<x-admin-form-select name="google_feed_condition" id="google_feed_status" title="GoogleFeed使用情况"
                     :value="old('google_feed_condition', $product->google_feed_condition ?? 'new')"
                     :options="[['title' =>'全新', 'id' => 'new'],['title' =>'翻新', 'id' => 'refurbished'],['title' =>'二手', 'id' => 'used']]"
                     key="id" label="title"/>
<x-admin-form-input name="google_feed_gtin" title="GTIN" :value="old('google_feed_gtin', $product->google_feed_gtin ?? '')"/>

