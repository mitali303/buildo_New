<div class="col-md-2">Date :</div>
<div class="col-md-3">
    <input type="text"
           id="Date"
           name="Date"
           class="form-control required">
</div>

<script>
flatpickr("#Date", {
    minDate: "{{ $minDate }}",
    dateFormat: "d-m-Y"
});
</script>
