SITEMAP.XML

1. sitemap-pages.xml    [low priority - small load]
  1.1 need cache refresh on event:
    STATIC_PAGE_URL_CHANGE
    STATIC_PAGE_CREATE
    STATIC_PAGE_DELETE
  1.2 store like - single static xml file....recreate whole file when change occur.              Language aware!
  1.3 logic of refresh - whole file as one...not much load
  1.4 special attention - NO
  1.5 need for deep level dir - NO        single file to store
  1.6. dir:  tmp/sitemap/pages/
  1.7 open remarks - NONE

2. continent(example c=2)          - shows ALL links for the continent (objects,articles and all)
   2.1 need cache referesh on event:
   EDO_PATH_CHANGED(marker,article) - add logic in updateEntry in api_marker and api_article
   PARENT_CHANGED(marker,article)   - add logic in updateEntry api_article,api_marker...  moved in another country/regions       (in this case should remove old one and not only update new one)
   CREATE(article,marker)               - done
   DELETE(article,marker)               - done

   2.2 store like - static xml file per  /continent e.g   2.xml   <- for europe          Language aware!
   2.3 logic of refresh - should be single entry refresh...not whole file!!!        For this old edo-path is required.  retrived with restGet history!
   2.4 special attention -  calling solr with rows,start  cause else will eat whole memory
   2.5 need for deep level dir - NO (only [num_conteinents] * 2 files total)
   2.6 dir: tmp/sitemap/continents/     all 14 files here..no need for deeper dirs
   2.7 open remarks -     google accepts only 10MB size / 50000 links            If that doesn't work can store map.2.xml in chunks of 500links or so
       First thing todo here is do for europe to see how large the file is
       We drop map here...only travel-guide pages shown


SITEMAP HTML
1. per continent
1.1 need cache refresh on event:
   EDO_PATH_CHANGED(marker) - add logic in updateEntry in api_marker
   NAME_CHANGED (marker)    - add logic in updateEntry in api_marker
   PARENT_CHANGED(marker)   - add logic in updateEntry...  moved in another country/regions       (in this case should remove old one and not only update new one)
   CREATE(marker)               - done
   DELETE(marker)               - done

1.2 store like - data for each country (all direct descendents).    and then combine all per continent.     This way should be easier todo country pagination.
    Language aware!
1.3 logic of refresh - get in which one country file is updated
1.4 special attention -  calling solr with rows,start  cause else will eat whole memory
1.5 need for deep level dir - probably.... quite many countries  EdoOS::getCanonical....
1.6 dir: tmp/sitemap/countries/limited
1.7 open remarks - NONE


ALL DESTINATIONS
1. per country
1.1 need cache refresh on event:
   EDO_PATH_CHANGED(marker) - add logic in updateEntry in api_marker
   NAME_CHANGED (marker)    - add logic in updateEntry in api_marker
   PARENT_CHANGED(marker)   - add logic in updateEntry...  moved in another country/regions       (in this case should remove old one and not only update new one)
   CREATE(marker)               - done
   DELETE(marker)               - done
1.2 store like - data for each country (all cities in each region etc  ??? no objects/no articles ???).   Language aware!
1.3 logic of refresh - get in which one country file is updated
1.4 special attention -  calling solr with rows,start  cause else will eat whole memory
1.5 need for deep level dir - probably.... quite many countries  EdoOS::getCanonical....
1.6 dir: tmp/sitemap/countries/all/
1.7 open remarks - NONE








MISC

define('MARKER_BIT_TYPE_WORLD', 1);
define('MARKER_BIT_TYPE_CONTINENT', 2);
define('MARKER_BIT_TYPE_COUNTRY', 4);
define('MARKER_BIT_TYPE_REGION', 8);
define('MARKER_BIT_TYPE_CITY', 16);
define('MARKER_BIT_TYPE_ISLAND', 32);
define('MARKER_BIT_TYPE_ISLAND_GROUP', 64);
define('MARKER_BIT_TYPE_OBJECT', 128);

1. sitemap - (continent only)               for each contents take all countries...        and     for each country take all      direct-descendents
2. all-destinations - shown in country pages only...          and of that country show all   the things..         cities and all...
3. sitemap.xml -> show absolutely everything + markers
