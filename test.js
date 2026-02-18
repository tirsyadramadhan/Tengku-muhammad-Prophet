jQuery(document).ready(function ($) {
  function updateCardStats() {
    $.ajax({
      url: '/api/dashboard-stats', // The route we created in Step 2
      method: 'GET',
      success: function (data) {
        // Options: useEasing, useGrouping, separator, decimal, etc.
        const moneyOptions = {
          prefix: 'Rp ',
          separator: '.',
          decimal: ',',
          duration: 2
        };

        const numberOptions = {
          duration: 2
        };

        // Animate each card
        // Note: data.incoming matches the keys in the JSON response
        new countUp.CountUp('card-incoming', data.incoming, numberOptions).start();
        new countUp.CountUp('card-price', data.price, moneyOptions).start();
        new countUp.CountUp('card-capital', data.capital, moneyOptions).start();
        new countUp.CountUp('card-margin', data.margin, moneyOptions).start();
      },
      error: function (err) {
        console.error('Failed to fetch stats', err);
      }
    });
  }

  jQuery(document).ready(function ($) {
    // 1. Trigger animation on initial page load
    updateCardStats();

    // 2. Trigger animation whenever DataTable reloads (Search, Pagination, or .ajax.reload())
    // Your table ID is '#table-incoming' based on the file provided
    $('#table-incoming').on('xhr.dt', function (e, settings, json, xhr) {
      updateCardStats();
    });
  });
  $(document).on('click', '.btn-delete-ajax', function () {
    let deleteUrl = $(this).data('url');
    let poNo = $(this).data('po');

    Swal.fire({
      title: 'Hapus Data?',
      text: `Apakah Anda yakin ingin menghapus PO #${poNo}?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, Hapus!',
      cancelButtonText: 'Batal',
      showLoaderOnConfirm: true, // Shows a loading spinner on the button
      preConfirm: () => {
        return $.ajax({
          url: deleteUrl,
          type: 'POST',
          data: {
            _method: 'DELETE',
            _token: '{{ csrf_token() }}'
          },
          success: function (response) {
            return response;
          },
          error: function (xhr) {
            // Pull the error message from the controller's JSON response
            let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
            Swal.showValidationMessage(`Request failed: ${msg}`);
          }
        });
      },
      allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
      if (result.isConfirmed) {
        Swal.fire('Terhapus!', result.value.message, 'success');
        $('#table-incoming').DataTable().ajax.reload(null, false);
      }
    });
  });
  var dt_table = $('#table-incoming');

  if (dt_table.length) {
    dt_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: "{{ route('incomingPo') }}",
      columns: [
        {
          data: 'DT_RowIndex',
          name: 'DT_RowIndex',
          orderable: false,
          searchable: false,
          className: 'text-center fw-medium text-muted'
        },
        {
          data: 'no_po',
          name: 'no_po'
        },
        {
          data: 'tgl_po',
          name: 'tgl_po'
        },
        {
          data: 'product_customer',
          name: 'nama_barang'
        },
        {
          data: 'qty',
          name: 'qty',
          className: 'text-center'
        },
        {
          data: 'total',
          name: 'total',
          className: 'text-end fw-bold bg-financial'
        },
        {
          data: 'modal_awal',
          name: 'modal_awal',
          className: 'text-end text-muted'
        },
        {
          data: 'margin',
          name: 'margin',
          className: 'text-end fw-bold text-success bg-profit'
        },
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false,
          className: 'text-center'
        }
      ],
      order: [[2, 'desc']],
      displayLength: 10,
      dom: '<"card-body d-flex flex-column flex-md-row justify-content-between align-items-center pt-0"<"me-md-2"l><"dt-action-buttons text-end"f>>t<"card-body d-flex flex-column flex-md-row justify-content-between"<"me-md-2"i><"p-0"p>>',
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json', // Indonesian language pack
        processing:
          '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
        search: '',
        searchPlaceholder: 'Cari...', // Indonesian placeholder
        sLengthMenu: '_MENU_',
        paginate: {
          next: '<i class="ri-arrow-right-s-line"></i>',
          previous: '<i class="ri-arrow-left-s-line"></i>'
        }
      }
    });
  }
});
