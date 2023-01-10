# Alpine_UrlRewrites

This extension provides a cli command for importing url rewrites from a csv file in magento.

### Cli command:
  * `alpine:url-rewrites:import`
  
### File location:
  * `./media/rewrites.csv`

### File format:
`"{target url}","{redirect url}"`

#### Notes:

If the target url does not include `en-us` or `en-mx` the entry will be omitted

If target url path is empty it will be constructed from the request url by removing the first url part, example
`foo/bar/baz` becomes `bar/baz`