<?php
/**
 * Compiles .po files in the languages/ directory to binary .mo files.
 * Usage: php bin/compile-mo.php [base_dir]
 *
 * .mo file format: https://www.gnu.org/software/gettext/manual/html_node/MO-Files.html
 */

// Allow passing a base directory as first argument for use from Docker
$base = isset($argv[1]) ? rtrim($argv[1], '/') : dirname(__DIR__);
$dir  = $base . '/languages';

if (!is_dir($dir)) {
    fwrite(STDERR, "Languages directory not found: $dir\n");
    exit(1);
}

$files = glob($dir . '/*.po');

if (empty($files)) {
    echo "No .po files found in $dir\n";
    exit(0);
}

foreach ($files as $po) {
    $mo = preg_replace('/\.po$/', '.mo', $po);
    compile_po_to_mo($po, $mo);
}

echo "Done.\n";

// ─── Functions ────────────────────────────────────────────────────────────────

function compile_po_to_mo(string $po_file, string $mo_file): void {
    $lines   = file($po_file, FILE_IGNORE_NEW_LINES);
    $entries = [];

    $msgid  = null;
    $msgstr = null;
    $in     = null; // 'id' | 'str'

    $flush = function() use (&$msgid, &$msgstr, &$entries) {
        if ($msgid !== null && $msgstr !== null && $msgstr !== '') {
            $entries[$msgid] = $msgstr;
        }
        $msgid  = null;
        $msgstr = null;
    };

    foreach ($lines as $line) {
        $line = rtrim($line);

        if (strncmp($line, 'msgid ', 6) === 0) {
            $flush();
            $in    = 'id';
            $msgid = po_unescape(substr($line, 6));
        } elseif (strncmp($line, 'msgstr ', 7) === 0) {
            $in     = 'str';
            $msgstr = po_unescape(substr($line, 7));
        } elseif ($line !== '' && $line[0] === '"' && $in !== null) {
            $segment = po_unescape($line);
            if ($in === 'id') {
                $msgid .= $segment;
            } else {
                $msgstr .= $segment;
            }
        }
        // blank lines, comments (#) → do nothing
    }
    $flush();

    // Separate header (empty msgid) from regular entries
    $header = $entries[''] ?? '';
    unset($entries['']);

    // Build originals + translations arrays; header goes first (index 0)
    $originals    = array_merge([''], array_keys($entries));
    $translations = array_merge([$header], array_values($entries));
    $n            = count($originals);

    // MO layout:
    //   [0]  magic         4 bytes  0x950412de (LE)
    //   [4]  revision      4 bytes  0
    //   [8]  n_strings     4 bytes
    //   [12] orig_offset   4 bytes  → points to orig descriptor table
    //   [16] trans_offset  4 bytes  → points to trans descriptor table
    //   [20] hash_size     4 bytes  0  (no hash table)
    //   [24] hash_offset   4 bytes
    //   [28] orig table    n × 8 bytes  (length, offset)
    //   [28 + n*8] trans table  n × 8 bytes
    //   [28 + n*16] string data (originals first, then translations)

    $orig_table_off  = 28;
    $trans_table_off = $orig_table_off  + $n * 8;
    $data_off        = $trans_table_off + $n * 8;

    // Concatenate null-terminated strings
    $orig_data  = '';
    $trans_data = '';
    foreach ($originals    as $s) { $orig_data  .= $s . "\x00"; }
    foreach ($translations as $s) { $trans_data .= $s . "\x00"; }

    // Build descriptors for originals (relative to $data_off)
    $orig_descs  = [];
    $trans_descs = [];

    $pos = 0;
    foreach ($originals as $s) {
        $len          = strlen($s);
        $orig_descs[] = [$len, $data_off + $pos];
        $pos         += $len + 1;
    }

    $pos = strlen($orig_data); // translations start after originals in the data block
    foreach ($translations as $s) {
        $len           = strlen($s);
        $trans_descs[] = [$len, $data_off + $pos];
        $pos          += $len + 1;
    }

    // Assemble binary
    $mo  = pack('V', 0x950412de);
    $mo .= pack('V', 0);
    $mo .= pack('V', $n);
    $mo .= pack('V', $orig_table_off);
    $mo .= pack('V', $trans_table_off);
    $mo .= pack('V', 0);
    $mo .= pack('V', $data_off); // hash table size=0, offset can be anything

    foreach ($orig_descs  as [$len, $off]) { $mo .= pack('VV', $len, $off); }
    foreach ($trans_descs as [$len, $off]) { $mo .= pack('VV', $len, $off); }

    $mo .= $orig_data . $trans_data;

    file_put_contents($mo_file, $mo);
    echo "Compiled: " . basename($mo_file) . " (" . count($entries) . " translated strings)\n";
}

function po_unescape(string $s): string {
    $s = trim($s);
    // strip surrounding double-quotes
    if (strlen($s) >= 2 && $s[0] === '"' && $s[-1] === '"') {
        $s = substr($s, 1, -1);
    }
    return str_replace(
        ['\\n',  '\\t', '\\"', '\\\\'],
        ["\n",   "\t",   '"',   '\\'],
        $s
    );
}
