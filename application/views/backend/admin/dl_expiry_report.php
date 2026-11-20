<?php
$status_meta = [
    'all'           => ['label' => get_phrase('all'),             'class' => 'badge-primary'],
    'expired'       => ['label' => get_phrase('expired'),         'class' => 'badge-danger'],
    'not_on_record' => ['label' => get_phrase('not_on_record'),   'class' => 'badge-dark'],
    'due_30'        => ['label' => get_phrase('due_in_30_days'),  'class' => 'badge-danger-lighten dl-due-30'],
    'due_60'        => ['label' => get_phrase('due_in_60_days'),  'class' => 'badge-warning dl-due-60'],
    'due_90'        => ['label' => get_phrase('due_in_90_days'),  'class' => 'badge-warning-lighten dl-due-90'],
    'valid'         => ['label' => get_phrase('valid'),           'class' => 'badge-success'],
];

// Builds a report URL keeping the current filters, with the given overrides
$report_url = function ($overrides = []) use ($filters) {
    $params = array_merge($filters, $overrides);
    foreach ($params as $k => $v) {
        if ($v === '' || ($v === 'all' && $k !== 'store_status')) unset($params[$k]);
    }
    return site_url('admin/dl_expiry_report') . (empty($params) ? '' : '?' . http_build_query($params));
};
?>
<style>
  .dl-chip {
    display: inline-flex;
    align-items: center;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 4px 8px;
    margin: 0 8px 8px 0;
    background: #fff;
    color: #343a40;
    transition: box-shadow .15s ease-in-out, border-color .15s ease-in-out;
  }
  .dl-chip:hover { color: #343a40; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
  .dl-chip.active { border-color: #727cf5; background: #f1f2fe; }
  .dl-chip .badge { font-size: 12px; font-weight: 600; padding: 4px 8px; }
  .dl-chip .dl-chip-count { margin-left: 8px; font-weight: 600; min-width: 20px; text-align: center; }
  .dl-due-30 { background: #fa5c7c !important; color: #fff !important; }
  .dl-due-60 { background: #fd7e14 !important; color: #fff !important; }
  .dl-due-90 { background: #ffbc00 !important; color: #fff !important; }
  .dl-critical { background: #6f42c1; color: #fff; font-size: 10px; letter-spacing: .3px; }
  #dl_expiry_table th, #dl_expiry_table td { white-space: nowrap; vertical-align: middle; }
  .dl-filter-form label { font-weight: 600; margin-right: 6px; }
  .dl-filter-form .form-control { min-width: 150px; }
</style>

<!-- Title -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title">
                    <i class="mdi mdi-certificate-outline title_icon"></i> DL Expiry Report
                    <a href="<?php echo site_url('admin/stores'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
                        <i class="mdi mdi-store mr-1"></i> <?php echo get_phrase('manage_stores'); ?>
                    </a>
                </h4>
                <p class="text-muted mb-0 mt-1">Drug licence (DL 20 / DL 21) position of every store, flagged before expiry</p>
            </div>
        </div>
    </div>
</div>

<!-- Status chips + filters -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body pb-2">
                <div class="d-flex flex-wrap">
                    <?php foreach ($status_meta as $key => $meta): ?>
                        <a href="<?php echo $report_url(['status' => $key]); ?>" class="dl-chip <?php echo $filters['status'] === $key ? 'active' : ''; ?>">
                            <span class="badge <?php echo $meta['class']; ?>"><?php echo $meta['label']; ?></span>
                            <span class="dl-chip-count"><?php echo number_format($counts[$key]); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <hr class="mt-1">

                <form method="get" action="<?php echo site_url('admin/dl_expiry_report'); ?>" class="form-inline dl-filter-form">
                    <input type="hidden" name="status" value="<?php echo html_escape($filters['status']); ?>">

                    <div class="form-group mr-3 mb-2">
                        <label for="filter_licence"><?php echo get_phrase('licence'); ?>:</label>
                        <select class="form-control form-control-sm" id="filter_licence" name="licence">
                            <option value="all"><?php echo get_phrase('all'); ?></option>
                            <option value="20" <?php if ($filters['licence'] === '20') echo 'selected'; ?>>DL 20</option>
                            <option value="21" <?php if ($filters['licence'] === '21') echo 'selected'; ?>>DL 21</option>
                        </select>
                    </div>

                    <div class="form-group mr-3 mb-2">
                        <label for="filter_zone"><?php echo get_phrase('zone'); ?>:</label>
                        <select class="form-control form-control-sm" id="filter_zone" name="zone">
                            <option value=""><?php echo get_phrase('all'); ?></option>
                            <?php foreach ($zones as $z): ?>
                                <option value="<?php echo html_escape($z); ?>" <?php if ($filters['zone'] === $z) echo 'selected'; ?>><?php echo html_escape($z); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mr-3 mb-2">
                        <label for="filter_state"><?php echo get_phrase('state'); ?>:</label>
                        <select class="form-control form-control-sm" id="filter_state" name="state">
                            <option value=""><?php echo get_phrase('all'); ?></option>
                            <?php foreach ($states as $s): ?>
                                <option value="<?php echo html_escape($s); ?>" <?php if ($filters['state'] === $s) echo 'selected'; ?>><?php echo html_escape($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mr-3 mb-2">
                        <label for="filter_category"><?php echo get_phrase('category'); ?>:</label>
                        <select class="form-control form-control-sm" id="filter_category" name="category">
                            <option value=""><?php echo get_phrase('all'); ?></option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo html_escape($c); ?>" <?php if ($filters['category'] === $c) echo 'selected'; ?>><?php echo html_escape($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mr-3 mb-2">
                        <label for="filter_store_status"><?php echo get_phrase('store_status'); ?>:</label>
                        <select class="form-control form-control-sm" id="filter_store_status" name="store_status">
                            <option value="1" <?php if ($filters['store_status'] === '1') echo 'selected'; ?>><?php echo get_phrase('active'); ?></option>
                            <option value="0" <?php if ($filters['store_status'] === '0') echo 'selected'; ?>><?php echo get_phrase('inactive'); ?></option>
                            <option value="all" <?php if ($filters['store_status'] === 'all') echo 'selected'; ?>><?php echo get_phrase('all'); ?></option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="mdi mdi-filter mr-1"></i><?php echo get_phrase('filter'); ?></button>
                        <a href="<?php echo site_url('admin/dl_expiry_report'); ?>" class="btn btn-sm btn-outline-secondary ml-1"><i class="mdi mdi-refresh mr-1"></i><?php echo get_phrase('reset'); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Report table -->
<?php
$table_rows = [];
foreach ($rows as $row) {
    $store = $row['store'];
    $days  = $row['days_left'];
    if ($days === null)  $days_text = '--';
    elseif ($days < 0)   $days_text = abs($days) . ' ' . get_phrase('days_overdue');
    elseif ($days === 0) $days_text = get_phrase('today');
    else                 $days_text = $days . ' ' . get_phrase('days');

    $table_rows[] = [
        'id'         => (int)$store['id'],
        'name'       => html_entity_decode((string)$store['store_name'], ENT_QUOTES, 'UTF-8'),
        'code'       => html_entity_decode((string)($store['store_code'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'region'     => trim(html_entity_decode(($store['zone'] ?? '') . ' / ' . ($store['state'] ?? ''), ENT_QUOTES, 'UTF-8'), ' /'),
        'licence'    => 'DL ' . $row['form_no'],
        'number'     => html_entity_decode($row['number'], ENT_QUOTES, 'UTF-8'),
        'expiry'     => $row['expiry'],
        'expiry_fmt' => $row['expiry'] !== '' ? date('d-m-Y', strtotime($row['expiry'])) : '',
        'days'       => $days,
        'days_text'  => $days_text,
        'status'     => $row['status'],
        'critical'   => in_array($row['status'], ['expired', 'due_30'], true),
    ];
}
?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-2" id="dl_export_buttons"></div>
                <div class="table-responsive">
                    <table class="table table-striped table-centered w-100 mb-0" id="dl_expiry_table">
                        <thead class="thead-light">
                            <tr>
                                <th><?php echo get_phrase('store'); ?></th>
                                <th><?php echo get_phrase('store_code'); ?></th>
                                <th><?php echo get_phrase('zone_/_state'); ?></th>
                                <th><?php echo get_phrase('licence'); ?></th>
                                <th><?php echo get_phrase('licence_no'); ?></th>
                                <th><?php echo get_phrase('expiry'); ?></th>
                                <th><?php echo get_phrase('days_left'); ?></th>
                                <th><?php echo get_phrase('status'); ?></th>
                                <th class="text-right no-export"><?php echo get_phrase('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var rows       = <?php echo json_encode($table_rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var statusMeta = <?php echo json_encode($status_meta, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var editUrl    = '<?php echo site_url('admin/store_form/edit_store_form/'); ?>';
    var updateText = '<?php echo get_phrase('update'); ?>';

    var esc = function(v) { return $('<div>').text(v == null ? '' : String(v)).html(); };
    var orDash = function(v) { return v === '' || v == null ? '--' : v; };

    var table = $('#dl_expiry_table').DataTable({
        data: rows,
        deferRender: true,
        pageLength: 25,
        order: [[6, 'asc']],
        columns: [
            { data: 'name', render: function(d, type, r) {
                if (type !== 'display') return d;
                return '<strong>' + esc(d) + '</strong>' +
                    (r.critical ? '<div><span class="badge dl-critical mt-1"><i class="mdi mdi-alert-circle mr-1"></i>CRITICAL</span></div>' : '');
            } },
            { data: 'code', render: function(d, type) { return type === 'display' ? esc(orDash(d)) : d; } },
            { data: 'region', render: function(d, type) { return type === 'display' ? esc(orDash(d)) : d; } },
            { data: 'licence', render: function(d, type) {
                return type === 'display' ? '<span class="badge badge-info"><i class="mdi mdi-file-certificate-outline mr-1"></i>' + esc(d) + '</span>' : d;
            } },
            { data: 'number', render: function(d, type) { return type === 'display' ? esc(orDash(d)) : orDash(d); } },
            { data: 'expiry', render: function(d, type, r) {
                if (type === 'sort' || type === 'type') return d || '9999-12-31';
                return type === 'display' ? esc(orDash(r.expiry_fmt)) : orDash(r.expiry_fmt);
            } },
            { data: 'days', render: function(d, type, r) {
                if (type === 'sort' || type === 'type') return d === null ? 999999 : d;
                if (type !== 'display') return r.days_text;
                return '<span class="' + (d !== null && d < 0 ? 'text-danger font-weight-bold' : '') + '">' + esc(r.days_text) + '</span>';
            } },
            { data: 'status', render: function(d, type) {
                var m = statusMeta[d];
                return type === 'display' ? '<span class="badge ' + m['class'] + ' px-2 py-1">' + esc(m.label) + '</span>' : m.label;
            } },
            { data: 'id', orderable: false, searchable: false, className: 'text-right no-export', render: function(d, type) {
                return type === 'display' ? '<a href="' + editUrl + d + '" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-pencil-outline mr-1"></i>' + esc(updateText) + '</a>' : '';
            } }
        ],
        buttons: [
            { extend: 'csvHtml5', text: '<i class="mdi mdi-file-delimited-outline mr-1"></i><?php echo get_phrase('export_csv'); ?>', className: 'btn btn-sm btn-outline-primary', title: 'DL_Expiry_Report_<?php echo date('Y-m-d'); ?>', bom: true, exportOptions: { columns: ':not(.no-export)', orthogonal: 'export' } },
            { extend: 'print', text: '<i class="mdi mdi-printer mr-1"></i><?php echo get_phrase('print'); ?>', className: 'btn btn-sm btn-outline-primary ml-1', title: 'DL Expiry Report (<?php echo date('d-m-Y'); ?>)', exportOptions: { columns: ':not(.no-export)', orthogonal: 'export' } }
        ],
        language: {
            emptyTable: "<?php echo get_phrase('no_matching_records_found'); ?>",
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        }
    });
    table.buttons().container().appendTo('#dl_export_buttons');
});
</script>
