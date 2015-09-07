import ogr
import sys
import csv

drv = ogr.GetDriverByName('ESRI Shapefile') #We will load a shape file
#ds_in = drv.Open("/Users/myeong/Dropbox/Event_Data/PittNeigh/Neighborhood.shp")    #Get the contents of the shape file
ds_in = drv.Open("/Users/myeong/git/meetup/QGIS-Census/neighborhood/Neighborhood.shp")
lyr_in = ds_in.GetLayer(0)    #Get the shape file's first layer

ds_in2 = drv.Open("/Users/myeong/git/meetup/QGIS-Census/cbsa/cb_2014_us_cbsa_5m.shp")
lyr_in2 = ds_in2.GetLayer(0)    #Get the shape file's first layer

ds_in3 = drv.Open("/Users/myeong/git/meetup/QGIS-Census/csa/cb_2014_us_csa_5m.shp")
lyr_in3 = ds_in3.GetLayer(0)    #Get the shape file's first layer

ds_in4 = drv.Open("/Users/myeong/git/meetup/QGIS-Census/subcon/tl_2011_42_cousub.shp")
lyr_in4 = ds_in4.GetLayer(0)    #Get the shape file's first layer

ds_in5 = drv.Open("/Users/myeong/git/meetup/QGIS-Census/county/PA_Counties_clip.shp")
lyr_in5 = ds_in5.GetLayer(0)    #Get the shape file's first layer

path = r'/Users/myeong/git/meetup/data/cleaning/Pitt_final_coor1.csv'
path2 = r'/Users/myeong/git/meetup/data/cleaning/Pitt_regions.csv'
cnt_list = [[0 for x in range(9)] for x in range(900)]

#Put the title of the field you are interested in here
idx_reg = lyr_in.GetLayerDefn().GetFieldIndex("HOOD")
idx_reg2 = lyr_in2.GetLayerDefn().GetFieldIndex("NAME")
idx_reg3 = lyr_in3.GetLayerDefn().GetFieldIndex("NAME")
idx_reg4 = lyr_in4.GetLayerDefn().GetFieldIndex("NAMELSAD")
idx_reg5 = lyr_in5.GetLayerDefn().GetFieldIndex("NAMELSAD")

#If the latitude/longitude we're going to use is not in the projection
#of the shapefile, then we will get erroneous results.
#The following assumes that the latitude longitude is in WGS84
#This is identified by the number "4236", as in "EPSG:4326"
#We will create a transformation between this and the shapefile's
#project, whatever it may be
geo_ref = lyr_in.GetSpatialRef()
geo_ref2 = lyr_in2.GetSpatialRef()
geo_ref3 = lyr_in3.GetSpatialRef()
geo_ref4 = lyr_in4.GetSpatialRef()
geo_ref5 = lyr_in5.GetSpatialRef()

point_ref=ogr.osr.SpatialReference()
point_ref.ImportFromEPSG(4326)
ctran=ogr.osr.CoordinateTransformation(point_ref,geo_ref)
ctran2=ogr.osr.CoordinateTransformation(point_ref,geo_ref2)
ctran3=ogr.osr.CoordinateTransformation(point_ref,geo_ref3)
ctran4=ogr.osr.CoordinateTransformation(point_ref,geo_ref4)
ctran5=ogr.osr.CoordinateTransformation(point_ref,geo_ref5)

def check(lon, lat):
    #Transform incoming longitude/latitude to the shapefile's projection
    [lon,lat,z]=ctran.TransformPoint(lon,lat)

    #Create a point
    pt = ogr.Geometry(ogr.wkbPoint)
    pt.SetPoint_2D(0, lon, lat)

    #Set up a spatial filter such that the only features we see when we
    #loop through "lyr_in" are those which overlap the point defined above
    lyr_in.SetSpatialFilter(pt)
    
    #Loop through the overlapped features and display the field of interest
    for feat_in in lyr_in:        
        return feat_in.GetFieldAsString(idx_reg)

def check_cbsa(lon, lat):
    #Transform incoming longitude/latitude to the shapefile's projection
    [lon,lat,z]=ctran2.TransformPoint(lon,lat)

    #Create a point
    pt = ogr.Geometry(ogr.wkbPoint)
    pt.SetPoint_2D(0, lon, lat)

    #Set up a spatial filter such that the only features we see when we
    #loop through "lyr_in" are those which overlap the point defined above
    lyr_in2.SetSpatialFilter(pt)
    
    #Loop through the overlapped features and display the field of interest
    for feat_in in lyr_in2:        
        return feat_in.GetFieldAsString(idx_reg2)

def check_csa(lon, lat):
    #Transform incoming longitude/latitude to the shapefile's projection
    [lon,lat,z]=ctran3.TransformPoint(lon,lat)

    #Create a point
    pt = ogr.Geometry(ogr.wkbPoint)
    pt.SetPoint_2D(0, lon, lat)

    #Set up a spatial filter such that the only features we see when we
    #loop through "lyr_in" are those which overlap the point defined above
    lyr_in3.SetSpatialFilter(pt)
    
    #Loop through the overlapped features and display the field of interest
    for feat_in in lyr_in3:        
        return feat_in.GetFieldAsString(idx_reg3)

def check_subcon(lon, lat):
    #Transform incoming longitude/latitude to the shapefile's projection
    [lon,lat,z]=ctran4.TransformPoint(lon,lat)

    #Create a point
    pt = ogr.Geometry(ogr.wkbPoint)
    pt.SetPoint_2D(0, lon, lat)

    #Set up a spatial filter such that the only features we see when we
    #loop through "lyr_in" are those which overlap the point defined above
    lyr_in4.SetSpatialFilter(pt)
    
    #Loop through the overlapped features and display the field of interest
    for feat_in in lyr_in4:        
        return feat_in.GetFieldAsString(idx_reg4)

def check_con(lon, lat):
    #Transform incoming longitude/latitude to the shapefile's projection
    [lon,lat,z]=ctran5.TransformPoint(lon,lat)

    #Create a point
    pt = ogr.Geometry(ogr.wkbPoint)
    pt.SetPoint_2D(0, lon, lat)

    #Set up a spatial filter such that the only features we see when we
    #loop through "lyr_in" are those which overlap the point defined above
    lyr_in5.SetSpatialFilter(pt)
    
    #Loop through the overlapped features and display the field of interest
    for feat_in in lyr_in5:        
        return feat_in.GetFieldAsString(idx_reg5)

#Take command-line input and do all this
#print check(-79.9542, 40.4406)


with open(path) as csvfile:
    line = csv.reader(csvfile, delimiter=',')
     
    i = 0    
    
    for row in line:        
        cnt_list[i][0] = row[0]
        cnt_list[i][1] = row[1] #venue name
        
        if row[2] == "":
            i += 1
            continue

        cnt_list[i][2] = row[3]; #lat        
        cnt_list[i][3] = row[2]; #lon
        cnt_list[i][4] = check(float(row[2]), float(row[3]))
        cnt_list[i][5] = check_cbsa(float(row[2]), float(row[3]))
        cnt_list[i][6] = check_csa(float(row[2]), float(row[3]))
        cnt_list[i][7] = check_subcon(float(row[2]), float(row[3]))
        cnt_list[i][8] = check_con(float(row[2]), float(row[3]))

        i += 1
    
    print("successful")
 
 
with open(path2, 'wb') as csvfile:
    writer = csv.writer(csvfile, delimiter=',')   
    for k in cnt_list:
        writer.writerow(k)
