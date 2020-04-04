# Reads in all the meetup data about groups and stores the results in a csv file.
# The CSV file contains the following fields:
# GID (unique identifier), description, members, group_id, category, tags, year, month
#


import json
import csv
import os




results_name= '/Users/johnmangino/Documents/myeong/meetup/data/group_results.csv'
results= open(results_name, 'w')
fieldnames = ['GID', 'description', 'members', 'group_id', 'category', 'tags', 'year', 'month', 'day']
writer = csv.DictWriter(results, fieldnames=fieldnames)
writer.writeheader()
 
# extract year from file name 
def get_year(file_name):
    idx = file_name.find("Group")
    
    return file_name[idx + 6 : idx + 10]
# extract month from file name    
def get_month(file_name):
    idx = file_name.find("Group")
    
    return file_name[idx + 11 : idx + 13]

def get_day(file_name):
    idx = file_name.find("Group")
    
    return file_name[idx + 14 : idx + 16]
    
# working directory containing raw data
path= '/Users/johnmangino/Documents/myeong/meetup/data/Groups/'
    
GID =1
for filename in os.listdir(path):
    #print filename

    if filename.find("json") == -1:
        continue
    
    file = open(path + filename)
    data = json.load(file)
    
    for element in data['results']:
        description= ""
        category= ""
        description= ""
        tag_list= ""
        
        try:
            description= element['description'].encode('utf-8').strip()
        except:
            description= ""
            
       
        members= element['members']
        group_id= element['id']
        
        try:
            category= element['category']['shortname'].encode('utf-8').strip()
        except:
            category= ""
            
       
       
    
        # create a list of tags separated by commas
        try:
            tags= element['topics']
            for x in range(0, len(tags)):
                if x != len(tags) - 1:
                    tag_list+= tags[x]['urlkey'].encode('utf-8').strip()+", "
                else:
                    tag_list+= tags[x]['urlkey'].encode('utf-8').strip()
        except:
            tag_list=""
                
        
        year= get_year(filename)
        month= get_month(filename)
        day= get_day(filename)
            
        try:
            writer.writerow({'GID': GID, 'description': description, 'members': members, 'group_id' : group_id, 'category' : category, 'tags': tag_list, 'year': year, 'month': month, 'day':day})
        except Exception, e:
            print e
        
        GID+= 1
    
    