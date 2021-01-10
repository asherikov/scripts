# Based on printnew.py
# 
# Parameters (must be defined in the config file):
#mailto         <mail@to>   # user@localhost
#mailfrom       <mail@from> # rawdog@localhost
#mailencoding   <encoding>  # koi8-r


import rawdoglib.rawdog
import rawdoglib.plugins

from rawdoglib.rawdog import encode_references

import datetime

from email.MIMEText import MIMEText


class Mailer:
    """email new articles"""
    def __init__(self):
        self.mailto = ''
        self.mailfrom = ''

    def article_added(self, rawdog, config, article, now):
        """Handle new articles using the article_seen hook."""
        mail_str = u''

        if (not self.mailto):
            return True

        feed = rawdog.feeds[article.feed]

        if article.entry_info.has_key('link'):
            mail_str += u'<a href="' + article.entry_info['link'] + '">'
        if article.entry_info.has_key('title'):
            mail_str += article.entry_info['title']
        if article.entry_info.has_key('link'):
            mail_str += u'</a><br /> <br />'


        if article.entry_info.has_key('content'):
            for content in article.entry_info["content"]:
                if "value" in content:
                    mail_str += content["value"]
                else:
                    if "value_raw" in content:
                        mail_str += content["value_raw"]
        elif article.entry_info.has_key('summary_detail'):
            mail_str +=  article.entry_info['summary_detail']['value']
        
        mail_str += u' [' + feed.get_html_link(config) + ']'

        #if article.entry_info.has_key('description'):
        #    mail_str += "<br>\n" + article.entry_info['description']
        # finish up the entry. I like a <hr> between each entry with some spacing.
        mail_str += "<p><hr><br clear=\"all\">\n"


        if (not self.mailto):
            return True

        if (mail_str == ''):
            return True

        # it seems that the maximum length of a line in the message 
        # is not handled properly when UTF is used
        msg = MIMEText(mail_str.encode(self.encoding, "xmlcharrefreplace"), "html", self.encoding)
        msg['Subject'] = article.feed
        msg['To'] = self.mailto
        msg['From'] = self.mailfrom

        if (not msg['From'] or not msg['To']):
            return True

        SENDMAIL = "/usr/sbin/sendmail" # sendmail location
        import os
        p = os.popen("%s -t" % SENDMAIL, "w")
        p.write(msg.as_string())
        sts = p.close()

        return True

    # We expect 'mailto' and 'mailfrom' to be in the config now.
    def config(self, config, name, value):
        if name == 'mailto':
            self.mailto = value
            return False
        elif name == 'mailfrom':
            self.mailfrom = value
            return False
        elif name == 'mailencoding':
            self.encoding = value
            return False
        else:
            return True

mailer = Mailer()

# actually attach our hooks now.
rawdoglib.plugins.attach_hook("article_added", mailer.article_added)

rawdoglib.plugins.attach_hook("config_option", mailer.config)
