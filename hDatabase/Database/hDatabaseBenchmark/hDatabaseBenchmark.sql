CREATE TABLE `hDatabaseBenchmark` (
  `hDatabaseBenchmark`             float(5,5)   NOT NULL,
  `hDatabaseBenchmarkTable`        varchar(55)  NOT NULL,
  `hDatabaseBenchmarkType`         varchar(15)  NOT NULL,
  `hDatabaseBenchmarkPossibleKeys` varchar(255) NOT NULL,
  `hDatabaseBenchmarkKey`          varchar(100) NOT NULL,
  `hDatabaseBenchmarkKeyLen`       int(5)       NOT NULL,
  `hDatabaseBenchmarkRef`          varchar(25)  NOT NULL,
  `hDatabaseBenchmarkRows`         int(5)       NOT NULL,
  `hDatabaseBenchmarkExtra`        varchar(100) NOT NULL,
  `hDatabaseBenchmarkQuery` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;