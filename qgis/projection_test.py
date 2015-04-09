import ogr
import sys
import csv

drv = ogr.GetDriverByName('ESRI Shapefile') #We will load a shape file
#ds_in = drv.Open("/Users/myeong/Dropbox/Event_Data/PittNeigh/Neighborhood.shp")    #Get the contents of the shape file
ds_in = drv.Open("C:/Users/LM/Dropbox2/Dropbox/Event_Data/PittNeigh/Neighborhood.shp")
lyr_in = ds_in.GetLayer(0)    #Get the shape file's first layer
path = r'C:\Users\LM\git\meetup\pitt_march_9_new.csv'
path2 = r'C:\Users\LM\git\meetup\pitt_march_9_neighbor.csv'
cnt_list = [[0 for x in range(5)] for x in range(700)]

#Put the title of the field you are interested in here
idx_reg = lyr_in.GetLayerDefn().GetFieldIndex("HOOD")

#If the latitude/longitude we're going to use is not in the projection
#of the shapefile, then we will get erroneous results.
#The following assumes that the latitude longitude is in WGS84
#This is identified by the number "4236", as in "EPSG:4326"
#We will create a transformation between this and the shapefile's
#project, whatever it may be
geo_ref = lyr_in.GetSpatialRef()
point_ref=ogr.osr.SpatialReference()
point_ref.ImportFromEPSG(4236)
ctran=ogr.osr.CoordinateTransformation(point_ref,geo_ref)

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

#Take command-line input and do all this
#print check(-79.9542, 40.4406)


with open(path) as csvfile:
    line = csv.reader(csvfile, delimiter=',')
     
    i = 0    
    
    for row in line:        
        cnt_list[i][0] = i
        cnt_list[i][1] = row[1] #venue name
        cnt_list[i][2] = row[2]; #lat        
        cnt_list[i][3] = row[3]; #lon
        cnt_list[i][4] = check(float(row[3]), float(row[2]))

        i += 1
    
    print("successful")
 
 
with open(path2, 'wb') as csvfile:
    writer = csv.writer(csvfile, delimiter=',')   
    for k in cnt_list:
        writer.writerow(k)