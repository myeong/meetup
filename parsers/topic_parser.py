import csv
import re

path = r'C:\Users\LM\Dropbox2\Dropbox\Event_Data\comparison\pittevent2_raw.csv'
path2 = r'C:\Users\LM\Dropbox2\Dropbox\Event_Data\comparison\Pittsburghevents2_new.csv'
cnt_list = [[0 for x in range(5)] for x in range(200)]

with open(path, newline='') as csvfile:
    line = csv.reader(csvfile, delimiter=',')
    

    