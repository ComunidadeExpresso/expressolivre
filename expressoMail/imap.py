import imaplib
import sys
import base64

host=sys.argv[1]
port=int(sys.argv[2])
user=sys.argv[3]
password=sys.argv[4]
date_time=int(sys.argv[5])
mailbox=sys.argv[6]
imap_file = open(sys.argv[7],"r")
tmp=imap_file.read()
message=base64.b64decode(tmp)
#print message
imap_file.close()
M = imaplib.IMAP4(host,port)
M.login(user,password)
flags=""
M.append(mailbox,flags,date_time,message)
#print mailbox+flags+date_time+message
M.logout()
#M.close()