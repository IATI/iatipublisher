<?php

return [
    'xls_data_storage_path'       => env('XLS_DATA_STORAGE_PATH', 'XlsImporter/tmp'),
    'xls_file_storage_path'       => env('XLS_FILE_STORAGE_PATH', 'XlsImporter/file'),
    'xml_file_storage_path'       => env('XML_FILE_STORAGE_PATH', 'XmlImporter/file'),
    'xml_data_storage_path'       => env('XML_DATA_STORAGE_PATH', 'XmlImporter/tmp'),
    'csv_file_storage_path'       => env('CSV_FILE_STORAGE_PATH', 'CsvImporter/file'),
    'csv_data_storage_path'       => env('CSV_DATA_STORAGE_PATH', 'CsvImporter/tmp'),
    'csv_file_local_storage_path' => env('CSV_FILE_LOCAL_STORAGE_PATH', 'CsvImporter/file'),
];
