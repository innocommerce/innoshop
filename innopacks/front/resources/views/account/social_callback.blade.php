<script>
  const url = "{{ front_route('account.index') }}";
  if (window.opener === null) {
    window.location.href = url;
  } else {
    window.opener.location = url;
    window.close();
  }
</script>
