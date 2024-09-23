
@for($i=1;$i<=$rating;$i++)
  <i class="bi-star-fill" style="color: var(--bs-primary);"></i>
@endfor

@for($i=1;$i<=5-$rating;$i++)
  <i class="bi-star" style="color: var(--bs-primary);"></i>
@endfor