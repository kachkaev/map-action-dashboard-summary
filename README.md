MapAction Dashboard Summary
========================

The app generates yaml files based on the google spreadsheets used by MapAction team members.
These files contain structured information on the volunteers’ availability and the events.

It is possible to generate new yamls, visualise the data track what has been changed for a selected period of time.

Structure of the files with summaries
----------------------------------

deployment_availability.2013-11-11_101315.yml
deployment_availability.2013-11-11_102010.yml
deployment_availability.latest.yml

```
volunteers:
    name:
        2013-11-12: yes
        2013-11-13: no
        2013-11-14: unknown
        2013-11-15: !asd
        #wrong = !raw string, e.g. asd → !asd (excl. mark helps to query wrong statuses easier)
    name2:
        2013-11-13: yes

date_window:
    start: 2013-11-20
    end: 2013-12-20

dates:
    2013-11-15:
        yes
            count: 10
            names: [name,name]
        no
            count: 10
            names: [name, name]
```

tasking_grid.2013-11-11_101315.yml
tasking_grid.2013-11-11_102010.yml
tasking_grid.latest.yml

```
events:
    -
        name:  Test
        time_start: 2013-11-10
        time_end: 2013-12-01
        location_name: London
        _location_lat: 51.2 #from nominatum
        _location_lon: -0.1 #from nominatum
        vol_reqs:    string
        availability:
            yes:
                count: 10
                names: [name,name]
            no:
                count: 2
                names: [name, name]
            possibly:
                count: 10
                names: [name, name]
            selected:
                count: 4
                names: [name, name]
            unknown:
                count: 10
                names: [name, name]
            wrong:
                count: 1
                names: [name, name]

    -
        name: Test2
        …
```

Usage
----------------------------------

```
 app/console mapaction:update-deployment-availability
```

```
 app/console mapaction:update-tasking-grid
```
