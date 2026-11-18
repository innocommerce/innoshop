{{-- Test fixture: renders the cover form component and pops the footer stack,
     so the pick-cover modal markup AND its pushed <script> land in one output. --}}
<x-common-form-image name="image" type="file" :value="$value" title="Cover"/>
@stack('footer')
