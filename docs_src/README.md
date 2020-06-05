This is one of a lot of gulpfiles.  
This uses pug.

# Usage

## gulp

Clean working files, directories.
```
gulp clean
```

Build release files to release directory.
```
gulp build
```

Build debug files to debug directory.  
Debug files is not minified and not concat.
```
gulp debug
```

Exec browsersync and watch changing.  
Watch targets are debug files.
```
gulp watch
```

## workflow

1. `gulp clean`
2. `gulp watch`
3. Add/Remove/Modify asset files.  
Asset files in `src`.
4. `gulp build`
5. Release files.  
Release files in `release`.  
If release files is strange, `gulp debug`, then check files in `debug`.


# License

My own project.  
Undistributable.
