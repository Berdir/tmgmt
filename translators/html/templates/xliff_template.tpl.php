<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>"; ?>
<xliff version='1.2' xmlns='urn:oasis:names:tc:xliff:document:1.2'>
  <file original='<?php echo $origin_file_name; ?>'
        source-language='<?php echo $source_language; ?>'
        target-language='<?php echo $target_language; ?>'
        datatype='plaintext'>
    <body>
      <?php // @todo Recheck xliff format ?>
      <?php // Somehow we need to know for which job strings are listed. ?>
      <?php // http://docs.oasis-open.org/xliff/v1.2/os/xliff-core.html ?>
      <?php foreach ($items as $item_key => $item): ?>
        <?php foreach ($item as $field_key => $field): ?>
          <trans-unit id='<?php echo $field_key; ?>'>
            <source><?php echo $field['#text']; ?></source>
          </trans-unit>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </body>
  </file>
</xliff>