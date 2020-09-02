# Reindexr

## Usage

* `bin/reindexr <prefix> <from-format> <to-format> --server=localhost --port=9200 --include-current=false`
  * formats: yearly, monthly, daily
  * default: daily => monthly
  * when: daily => yearly: daily => monthly => yearly
  * when `--include-current=false` the current `to-format` (month|year) will be skipped
    * examples:
      * ```bash
        # today: 26.05.2020
        bin/reindexr <prefix> daily monthly --include-current=false
        # will reindex all daily indices < 01.05.2020 into their monthly counterparts [<prefix>2020-04-(01-30) -> <prefix>2020-04]
        ## skips the current month! ##  
        ```
      * ```bash
        # today: 26.05.2020
        bin/reindexr <prefix> daily monthly --include-current=true
        # will reindex all daily indices from today into their monthly counterparts [<prefix>2020-05-(01-26) -> <prefix>2020-05]
        ## includes the current month! ##        
        ```
      * ```bash
        # today: 26.05.2020
        bin/reindexr <prefix> monthly yearly --include-current=false
        # will reindex all monthly indices < 01.01.2020 into their yearly counterparts [<prefix>2019-(01-12) -> <prefix>2019]
        ## skips the current year! ##        
        ```
        
