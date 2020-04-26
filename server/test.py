#!/usr/bin/env python
# -*- coding: utf-8 -*-

import requests, json
from bs4 import BeautifulSoup
import urllib
import urllib.request
from urllib.parse import urlparse
from urllib.parse import urljoin
from urllib.request import urlretrieve, urlopen, Request
from os import makedirs
import os.path, time, re
import datetime
from datetime import datetime
import calendar
from googletrans import Translator
import traceback
import re
import time
import json
import sys
import codecs
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support.expected_conditions import presence_of_element_located
from webdriver_manager.chrome import ChromeDriverManager

WEB_HOOK_URL = "https://hooks.slack.com/services/xxxxxxxxx/xxxxxxxxxx/xxxxxxx"

def l2str(s):
    str1 = ""
    for ele in s:
        str1 += ele
    return str1

def title2id(s):
    id = ""
    for t in s.split(" "):
        id += t[0]
    return id

def ncis():
    array = []
    try:
        sys.stdout = codecs.getwriter('utf-8')(sys.stdout)
        options = webdriver.ChromeOptions()
        options.add_argument('--headless')
        options.add_argument('--no-sandbox')
        options.add_argument('--disable-dev-shm-usage')
        options.set_headless(True)
        wd =  webdriver.Chrome(executable_path='/home/scraping/bin/chromedriver',chrome_options=options)
        wd.get("http://ncis.nier.go.kr/main.do")
        wd.find_element_by_xpath('//*[@id="contents"]/div[3]/div/div[1]/a').click()
        wait = WebDriverWait(wd,1)
        html = wd.page_source
        soup = BeautifulSoup(html, "html.parser")
        tmp = soup.find("table",class_="tbl1").find_all("tr")
        tmp.pop(0)
        for a in tmp:
            tmp2 = a.find_all("td")
            id = "ncis1-"+tmp2[0].text
            title = tmp2[1].a.text
            updated = tmp2[3].text
            array.append({
                "title":title,
                "id":id,
                "updated": updated,
                "site":"NCIS",
                "link":"http://ncis.nier.go.kr/main.do"
            })
        wd.get("http://ncis.nier.go.kr/bbs/bbsLawTabList.do")
        wd.find_element_by_xpath('//*[@id="contents"]/div/ul/li[2]/a').click()
        wait = WebDriverWait(wd,1)
        html = wd.page_source
        soup = BeautifulSoup(html, "html.parser")
        tmp = soup.find("table",class_="tbl1").find_all("tr")
        tmp.pop(0)
        for a in tmp:
            tmp2 = a.find_all("td")
            id = "ncis2-"+tmp2[0].text
            title = tmp2[1].a.text
            updated = tmp2[3].text
            array.append({
                "title":title,
                "id":id,
                "updated": updated,
                "site":"NCIS",
                "link":"http://ncis.nier.go.kr/bbs/bbsLawTabList.do"
            })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

def ec():
    array = []
    try:
        url = "https://ec.europa.eu/environment/chemicals/index_en.htm"
        l = urllib.request.urlopen(url)
        e = l.info().get_content_charset(failobj="utf-8")
        html = l.read().decode(e)
        soup = BeautifulSoup(html, "html.parser")
        atitle = soup.find(id="announces8").ul.li.find_all("a")
        otitle = soup.find(id="announces8").ul.li
        tmp = otitle.find_all("li")
        now = datetime.now()
        today = now.strftime('%Y-%m-%d')
        for a in tmp:
            title = l2str(a.find_all(text=True)).replace("\n","")
            id = title2id(title)
            link = "https://ec.europa.eu" + a.a["href"]
            if(a.a["href"][0] != '/'):
                link = a.a["href"]
                array.append({
                    "title": title,
                    "id": id,
                    "updated": today,
                    "site":"EC",
                    "link":link
                })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

def echa():
    array = []
    try:
        url = "https://echa.europa.eu/news"
        l = urllib.request.urlopen(url)
        e = l.info().get_content_charset(failobj="utf-8")
        html = l.read().decode(e)
        soup = BeautifulSoup(html, "html.parser")
        tmp = soup.find(class_="NewsLevelA")
        if(tmp is not None):
            tmp = soup.find(class_="NewsLevelA").find("dl")
            tmp2 = tmp.dt.a["href"].split("/")
            id = tmp2[len(tmp2)-1]
            datetmp = tmp.find(class_="NewsDate").text.split("/")
            update = datetmp[2] + "-" + datetmp[1] + "-" + datetmp[0]
            array.append({
                "title":tmp.dt.a.text,
                "id":id,
                "updated":update,
                "site":"ECHA",
                "link":"http://echa.europa.eu"+tmp.dt.a["href"]
            })
        tmp = soup.find_all(class_="NewsLevelB")
        if(tmp is not None):
            for a in tmp:
                tmp2 = a.find("dl").dt.a
                tmp3 = tmp2["href"].split("/")
                id = tmp3[len(tmp3)-1]
                datetmp = a.find(class_="NewsDate").text.split("/")
                update = datetmp[2] + "-" + datetmp[1] + "-" + datetmp[0]
                array.append({
                    "title":tmp2.text,
                    "id":id,
                    "updated": update,
                    "site":"ECHA",
                    "link":"http://echa.europa.eu"+tmp2["href"]
                })
        if(tmp is not None):
            tmp = soup.find(class_="NewsLevelC").find_all("dl")
            for a in tmp:
                tmp2 = a.dt.a
                tmp3 = tmp2["href"].split("/")
                id = tmp3[len(tmp3)-1]
                datetmp = a.find(class_="NewsDate").text.split("/")
                update = datetmp[2] + "-" + datetmp[1] + "-" + datetmp[0]
                array.append({
                    "title":tmp2.text,
                    "id":id,
                    "updated": update,
                    "site":"ECHA",
                    "link":"http://echa.europa.eu"+tmp2["href"]
                })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

def epa():
    array = []
    try:
        url = "https://www.epa.gov/newsreleases/search"
        l = urllib.request.urlopen(url)
        e = l.info().get_content_charset(failobj="utf-8")
        html = l.read().decode(e)
        soup = BeautifulSoup(html, "html.parser")
        class1 = "panel-pane pane-views-panes pane-search-news-releases-panel-pane-1"
        tmp = soup.find(class_=class1)
        tmp = tmp.find_all("div", class_="views-row")
        for a in tmp:
            tmp2 = a.find("a")
            tmp3 = tmp2["href"].split("/")
            id = tmp3[len(tmp3)-1]
            datetmp = a.find(class_="date-display-single").text.split("/")
            update = datetmp[2] + "-" + datetmp[0] + "-" + datetmp[1]
            array.append({
                "title":tmp2.text,
                "id":id,
                "updated": update,
                "site":"EPA",
                "link":"https://www.epa.gov"+tmp2["href"]
            })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

def mepscc():
    array = []
    try:
        tr = Translator()
        url = "http://www.mepscc.cn/zxly/hxpzwh/"
        l = urllib.request.urlopen(url)
        e = l.info().get_content_charset(failobj="utf-8")
        html = l.read().decode(e)
        soup = BeautifulSoup(html, "html.parser")
        tmp = soup.find(class_="list").find_all("li")

        for a in tmp:
            tmp2 = a.find("a")
            tmp3 = tmp2["href"].split("/")
            id = "mepscc-"+tmp3[len(tmp3)-1][:-6]
            title = tr.translate(tmp2.text, dest='ja').text
            updated = a.find(class_="fr").text[1:-1]
            array.append({
                "title":title,
                "id":id,
                "updated": updated,
                "site":"MEP-SCC",
                "link":"http://www.mepscc.cn/zxly/hxpzwh"+tmp2["href"][1:]
            })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

def osha():
    array = []
    try:
        url = "https://www.osha.gov.tw/1106/1113/1114/"
        l = urllib.request.urlopen(url)
        e = l.info().get_content_charset(failobj="utf-8")
        html = l.read().decode(e)
        soup = BeautifulSoup(html, "html.parser")
        tmp = soup.find("table",summary="").tbody.find_all("tr")
        for a in tmp:
            tmp2 = a.find_all("td")
            tmp3 = tmp2[2].text.split("-")
            tmp4 = tmp2[0].a
            title = tmp4.text
            tmp5 = tmp4["href"].split("/")
            id = "osha-"+tmp5[-2]
            updated = str(int(tmp3[0])+1911)+"-"+tmp3[1]+"-"+tmp3[2]
            array.append({
                "title": title,
                "id": id,
                "updated": updated,
                "site":"労働部職業安全衛生署",
                "link":"https://www.osha.gov.tw"+tmp4["href"]
            })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

def chemreg():
    array = []
    try:
        url = "https://chemreg-border.epa.gov.tw/content/info/NewsList.aspx"
        l = urllib.request.urlopen(url)
        e = l.info().get_content_charset(failobj="utf-8")
        html = l.read().decode(e)
        soup = BeautifulSoup(html, "html.parser")
        tmp = soup.find_all("div", class_="news_list")
        for a in tmp:
            title = a.find(class_="sub_title").text[30:-26]
            tmp2 = a.find(class_="more").a["href"]
            id = "chemreg-"+tmp2.split("=")[1]
            tmp3 = a.find(class_="title").text.split(" ")
            updated = tmp3[54][:-2].replace("/","-")
            link = "https://chemreg-border.epa.gov.tw/content/info/"
            link += a.find(class_="more").a["href"]
            array.append({
                "title": title,
                "id": id,
                "updated": updated,
                "site":"Chem-reg",
                "link":link
            })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

def nite():
    array = []
    try:
        url = "https://www.nite.go.jp/chem/index.html"
        l = urllib.request.urlopen(url)
        e = l.info().get_content_charset(failobj="utf-8")
        html = l.read().decode(e)
        soup = BeautifulSoup(html, "html.parser")
        tmp = soup.find(class_="box-border").find(class_="list-date addborder fifth-contents")
        tmp = tmp.find_all(["dd","dt"])
        tmp4 = []
        for i in range(int(len(tmp)/2)):
            tmp4.append(str(tmp[2*i])+str(tmp[2*i+1]))
        for a in tmp4:
            a = BeautifulSoup(a, "html.parser")
            title = a.dd.a.text
            tmp2 = a.dd.a["href"]
            tmp3 = tmp2.split("/")
            id = "nite-"+tmp3[len(tmp3)-1][:-5]
            updated = a.find("dt").text.replace("年","-").replace("月","-")[:-1].split("-")
            year  = int(updated[0])
            month = int(updated[1])
            day   = int(updated[2])
            updated = '{:0=4}-{:0=2}-{:0=2}'.format(year,month,day)
            link = "https://www.nite.go.jp"+tmp2
            array.append({
                "title": title,
                "id": id,
                "updated": updated,
                "site":"NITE",
                "link":link
            })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

def meti():
    array = []
    try:
        headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.3'}
        reg_url = "https://www.meti.go.jp/policy/chemical_management/kasinhou/index.html"
        req = Request(url=reg_url, headers=headers)
        html = urlopen(req).read()
        soup = BeautifulSoup(html, "html.parser")
        tmp = soup.find_all("ul",class_="lnkLst")[3].find_all("a")[0:9]
        lupdated = []
        for i in range(len(tmp)):
            a = tmp[i]
            title = a.text
            id = "meti-"+a["href"].split("/")[-1]
            updated = re.search('\d{2}/\d{1,2}/\d{1,2}', title)
            if(updated is not None):
                updated = updated.group(0).split("/")
                year  = int("20"+updated[0])
                month = int(updated[1])
                day   = int(updated[2])
                updated = '{:0=4}-{:0=2}-{:0=2}'.format(year,month,day)
                lupdated.append(updated)
            else:
                updated = lupdated[-1][0:7]
            link = "https://www.meti.go.jp"+a["href"]
            array.append({
                "title": title,
                "id": id,
                "updated": updated,
                "site":"化審法",
                "link":link
            })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

def meti2():
    array = []
    try:
        headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.3'}
        reg_url = "https://www.meti.go.jp/policy/chemical_management/law/index.html"
        req = Request(url=reg_url, headers=headers)
        html = urlopen(req).read()
        soup = BeautifulSoup(html, "html.parser")
        tmp = soup.find_all("ul",class_="lnkLst")[3].find_all("a")[0:9]
        lupdated = []
        for a in tmp:
            title = a.text
            id = "meti-"+a["href"].split("/")[-1]
            updated = re.search('\d{4}\.\d{1,2}\.\d{1,2}', title)
            if(updated is not None):
                updated = updated.group(0).split(".")
                year  = int(updated[0])
                month = int(updated[1])
                day   = int(updated[2])
                updated = '{:0=4}-{:0=2}-{:0=2}'.format(year,month,day)
                lupdated.append(updated)
            else:
                updated = lupdated[-1][0:7]
            link = "https://www.meti.go.jp"+a["href"]
            array.append({
                "title": title,
                "id": id,
                "updated": updated,
                "site":"化管法",
                "link":link
            })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

def mhlw():
    array = []
    try:
        url = "https://anzeninfo.mhlw.go.jp/index.html"
        l = urllib.request.urlopen(url)
        html = l.read()
        soup = BeautifulSoup(html, "html.parser")
        tmp = soup.find(id="update").find_all(["dd","dt"])
        tmp4 = []
        for i in range(int(len(tmp)/2)):
            tmp4.append(str(tmp[2*i])+str(tmp[2*i+1]))
        for a in tmp4:
            br = BeautifulSoup(a, "html.parser").find("br/")
            if(br is not None):
                br.decompose()
                a = br
            else:
                a = BeautifulSoup(a, "html.parser")
            title = a.dd.text
            id = "mhlw-"+title[0:15]
            thismonth = datetime.today().month
            thisyear = datetime.today().year
            year  = int(thisyear)
            month = int(re.search('\d{1,2}月', a.dt.text).group(0)[:-1])
            day   = int(re.search('\d{1,2}日', a.dt.text).group(0)[:-1])
            if(month > thismonth):
                year -= 1
            updated = '{:0=4}-{:0=2}-{:0=2}'.format(year,month,day)
            link = "https://anzeninfo.mhlw.go.jp/html/news.html"
            array.append({
                "title": title,
                "id": id,
                "updated": updated,
                "site":"安衛法",
                "link":link
            })
    except:
        requests.post(WEB_HOOK_URL, data = json.dumps({
            'text': 'Notifycation From Python.\n```'+traceback.format_exc()+'```',  #通知内容
            'username': 'Scraping Traceback',
            'icon_emoji': ':warning:',
            'link_names': 1,
            }))
        pass
    return array

if __name__ == "__main__":
    try:
        array = []
        array += ncis()
        array += ec()
        array += echa()
        array += epa()
        array += mepscc()
        array += osha()
        array += chemreg()
        array += nite()
        array += meti()
        array += meti2()
        array += mhlw()
        #print(json.dumps(array))
    except:
        pass

    with open("data", mode='w') as f:
        json.dump(array,f,indent=2)
        f.close()

        
