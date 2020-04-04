# Reads in all the meetup data about events and stores the results in a csv file.
# The CSV file contains the following fields:
# EID (unique identifier), GID, group_id, event_id, description, date, time, location
# 





import json
import csv
import os
from time import gmtime, strftime
import time

path= '/Users/johnmangino/Documents/myeong/meetup/data/Events/'

csvfile= open('/Users/johnmangino/Documents/myeong/meetup/data/group_results.csv')
reader = csv.DictReader(csvfile)
hash= {}


results_name= '/Users/johnmangino/Documents/myeong/meetup/data/events_results.csv'
results= open(results_name, 'w')
fieldnames = ['EID', 'GID', 'group_id', 'event_id', 'description', 'date', 'time', 'location']
writer = csv.DictWriter(results, fieldnames=fieldnames)
writer.writeheader()

# I store data in a hash to increase speed of looking up data
for row in reader:
    if int(row['group_id']) in hash:
        hash[int(row['group_id'])].append(row)
        
    else:
        hash[int(row['group_id'])]= [row]
    
    


#extracts year from file name
def get_year(file_name):
    idx = file_name.find("Events")
    
    return int(file_name[idx + 7 : idx + 11])
# extracts month from file name    
def get_month(file_name):
    idx = int(file_name.find("Events"))
    
    return int(file_name[idx + 12 : idx + 14])
    
def get_day(file_name):
    idx = file_name.find("Events")
    
    return int(file_name[idx + 15 : idx + 17])    



EID= 1
for filename in os.listdir(path):
    if filename.find("json") == -1:
        continue

    file = open(path + filename)
    data = json.load(file)

    
    for element in data['results']:
        event_description= element['description'].encode('utf-8').strip() if 'description' in element else ""
        month= get_month(filename)
        year= get_year(filename)
        day= get_day(filename)
        group_id= int(element['group']['id'])
        GID= -1
        t= element['time']
        event_id= element['id'] if 'id' in element else ''
    
        event_date= strftime("%d %b %Y", time.localtime(t/1000))
        event_time=  strftime("%H:%M", time.localtime(t/1000))
    
        address_1= element['venue']['address_1'].encode('utf-8').strip() if 'venue' in element else ""
        city= element['venue']['city'].encode('utf-8').strip() if 'venue' in element else ""
    
    
        location= address_1+" "+ city 
        
        
    
        if group_id not in hash:
            continue
        length= len(hash[group_id])
        idx = 0
    
        while idx < length and GID == -1:
            map= hash[group_id][idx]
        

            if group_id== int(map['group_id']) and int(map['month']) == month and int(map['year']) == year and int(map['day']) == day:
                GID= map['GID']
                writer.writerow({'EID': EID, 'GID': GID, 'group_id': group_id, 'event_id' : event_id, 'description' : event_description, 'date': event_date, 'time': event_time, 'location': location})
                
                
                
    
            idx+= 1
        
        EID+= 1
