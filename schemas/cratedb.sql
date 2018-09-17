create table statusengine_perfdata (
    hostname string,
    service_description string,
    label string,
    timestamp  timestamp,
    timestamp_unix timestamp,
    day as date_trunc('day', timestamp),
    value double,
    unit string
) clustered into 4 shards partitioned by (day) with (number_of_replicas = '0');