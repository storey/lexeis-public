# -*- coding: utf-8 -*-
# Parse the text file containing Betant's info
import re
import xml.etree.ElementTree as ET

import utils
from parseDefinition import LEVEL_ITEMS

# true if matching Betant info should not be used for this lemma
def ignoreBetant(lemma):
    return (lemma == "Δῆλος" or lemma == "Ἕλος" or lemma == "Ἐπίκουρος" or
            lemma == "Εὔβουλος" or lemma == "Ἠπειρωτικός" or lemma == "Ἵππαρχος"
            or lemma == "Ἄκρα" or lemma == "Κῦρος" or lemma == "Μητρόπολις"
            or lemma == "Ὅμηρος" or lemma == "οὗ2" or lemma == "Παράλιος"
            or lemma == "Πόλις" or lemma == "Πόντος" or lemma == "Πρόξενος" or
            lemma == "προσδέω" or lemma == "Τέως" or lemma == "ἄπειρος2" or
            lemma == "Εὐφήμως" or lemma == "ἔφοδος" or lemma == "ἔφοδος2" or
            lemma == "θύω2" or lemma == "καταδέω2" or lemma == "κύριος2" or
            lemma == "περίπλοος" or lemma == "πυρά" or lemma == "χερσόνησος"
            or lemma == "δέω2")

keyMap = {
    "taxe/ws,taxu/s": "taxu/s",
    "pre/sbus,presbeuth/s": "presbeuth/s",
    "pa/reimi2,pari/hmi,pare/rxomai": "pa/reimi",
    "yhfi/zomai": "yhfi/zw",
    "xari/zomai": "xari/zw",
    "filoneiki/a": "filoniki/a",
    "filoneike/w": "filonike/w",
    "u(poni/fw": "u(ponei/fw",
    "u(pomi/gnumi": "u(pomei/gnumi",
    "u(podeh/s1": "u(podeh/s",
    "u(pe/r-tei/nw": "u(pertei/nw",
    "ta/fos1": "ta/fos",
    "sw/zw": "sw/|zw",
    "se/bw": "se/bomai",
    "r(a|qumi/a": "r(aqumi/a",
    "pw=s1,pws2": "pw=s",
    "prwi/": "prwi+/",
    "proupa/rxw": "prou+pa/rxw",
    "pro/plous": "pro/plous",
    "pro/-ka/qhmai": "proka/qhmai",
    "proi/sxw": "proi+/sxw",
    "proi/sthmi": "proi+/sthmi",
    "προίημι": "proi+/hmi",
    "pro/eimi1,proe/rxomai": "pro/eimi",
    "proqnh/skw": "proqnh/|skw",
    "proecai/ssw": "proecai+/ssw",
    "proa/steion": "proa/stion",
    "polu/s,plei/wn,plei=stos": "polu/s",
    "plwi/zw": "plwi+/zw",
    "plou=tos1": "plou=tos",
    "peri/plous": "peri/ploos",
    "peri/-i)/sxw": "perii/sxw",
    "peri/eimi1": "peri/eimi",
    "penthko/ntoros": "penthko/nteros",
    "pa/reimi1": "pa/reimi1",
    "panstratia/": "panstratia/|",
    "pai/w1": "pai/w",
    "*paia/n": "paia/n",
    "o(ra/w,ei)=don": "o(ra/w",
    "o(lko/s2": "o(lko/s",
    "oi)su/inos": "oi)su/i+nos",
    "o)isto/s": "o)i+sto/s",
    "sunepilamba/nomai": "sunepilamba/nomai",
    "su/neimi1": "su/neimi",
    "sumpa/reimi1": "sumpa/reimi",
    "summi/gnumi": "summei/gnumi",
    "su/n-ka/qhmai": "sugka/qhmai",
    "nhi/ths": "nhi+/ths",
    "*mhdi/zw": "mhdi/zw",
    "*mhdismo/s": "mhdismo/s",
    "me/teimi1": "me/teimi",
    "melito/omai": "melito/w",
    "la/w1,la/w2": "la/w2",
    "lhi/zomai": "lhi+/zomai",
    "le/gw2,ei)=pon,e)rw=": "le/gw",
    "lampa/s1": "lampa/s",
    "klei/w1": "klei/w",
    "katadama/zomai": "katadama/zw",
    "katabia/zomai": "katabia/zw",
    "karpo/s1": "karpo/s",
    "kalw/dion": "kalw/|dion",
    "kako/nous": "kako/noos",
    "ei)=mi,e)/rxomai": "ei)=mi",
    "θύω1": "qu/w",
    "qnh/skw": "a)poqnh/|skw",
    "θεῖος1": "qei=os",
    "θεῖον1": "qei=on",
    "θᾶσσον, θάσσων": "taxu/s",
    "h(de/ws": "h(du/s",
    "za/w": "zh/w",
    "e)/fodos1": "e)/fodos",
    "*eu)/ripos": "eu)/ripos",
    "eu)/nous": "eu)/noos",
    "eu)qe/ws": "eu)qu/s",
    "e)/sodos": "ei)/sodos",
    "ei)/seimi,ei)se/rxomai": "ei)/seimi",
    "*(ermh=s-pl": "Ἑρμῆς",
    "e)pimi/gnumi": "e)pimei/gnumi",
    "e)pikata/gomai": "e)pikata/gw",
    "e)pi/-ka/qhmai": "e)pika/qhmai",
    "e)pi/-kaqi/sthmi": "e)pikaqi/sthmi",
    "e)pe/ceimi,e)pece/rxomai": "e)pe/ceimi",
    "e)/ceimi1,e)ce/rxomai": "e)/ceimi",
    "e)nanti/on": "e)nanti/os",
    "e)/mpnous": "e)/mpnoos",
    "*(ellhnotami/ai": "Ἑλληνοταμίαι",
    "e)/rgw1": "e)/rgw",
    "du/snous": "du/snoos",
    "du/w2": "du/w",
    "dii/sthmi": "dii+/sthmi",
    "diikne/omai": "dii+kne/omai",
    "diayhfi/zomai": "diayhfi/zw",
    "diapi/mplamai": "diapi/mplhmi",
    "diani/stamai": "diani/sthmi",
    "diale/gw": "diale/gomai",
    "diabouleu/omai": "diabouleu/w",
    "dhio/w": "dhi+o/w",
    "de/omai": "δέω2",
    "dai/s1": "dai+/s",
    "*dareiko/s": "dareiko/s",
    "*gumnopaidi/ai": "gumnopaidi/ai",
    "*bore/as": "bore/as",
    "*bore/as-pl": "bore/as",
    "*boiwta/rxhs": "boiwta/rxhs",
    "*boiwtarxe/w": "boiwtarxe/w",
    "*)attikismo/s": "Ἀττικισμός",
    "*)attiki/zw": "Ἀττικίζω",
    "*)arktou=ros-pl": "Ἀρκτοῦρος",
    "a)poski/dnamai": "a)poski/dnhmi",
    "a)pomimnh/skomai": "a)pomimnh/|skomai",
    "a)pokindu/neusis": "a)pokinduneu/w",
    "a)poqnh/skw": "a)poqnh/|skw",
    "a)/peimi2,a)pe/rxomai": "a)/peimi2",
    "a)pe/xqomai": "a)pexqa/nomai",
    "a)/peiros1": "a)/peiros",
    "a)/peimi1": "a)/peimi",
    "a)oido/s1": "a)oido/s",
    "a)ntepiteixi/zomai": "a)ntepiteixi/zw",
    "a)moqei/": "a(mo/qi",
    "a)/llws": "a)/llos",
    "a)/llh|": "a)/llos",
    "a)kribw=s": "a)kribh/s",
    "a)i/dios": "a)i+/dios",
    "ἄδεσμος φυλακή": "ἄδεσμος",
    "a)krobo/lisis": "a)krobolismo/s",
    "a)/llote": "a)/llos",
    "a)/lloqi": "a)/llos",
    "a)/lloqen": "a)/llos",
    "a)moqi/": "a(mo/qi",
    "a)mfote/rwqen": "a)mfo/teros",
    "a)nalo/w": "a)nali/skw",
    "a)neime/nws": "a)ni/hmi",
    "a)nqekte/on": "a)nte/xw",
    "a)/neimi": "a)ne/rxomai",
    "a)ntiple/w": "a)ntepiple/w",
    "a)peoiko/tws": "a)peiko/tws",
    "a(plw=s": "a(plo/os",
    "a)podu/nw": "a)podu/w",
    "a)/poqen": "a)/pwqen",
    "a)po/mnumi": "e)po/mnumi",
    "a)postate/on": "a)postate/w",
    "a)pofqei/rw": "diafqei/rw",
    "a)pofra/gnumi": "a)pofa/rgnumi",
    "a)poxra/omai": "a)poxra/w",
    "a)sqene/w": "a)sqeno/w",
    "au)to/qen": "au)to/s",
    "au)to/qi": "au)to/s",
    "au)tou=": "au)to/s",
    "bouleute/on": "bouleu/w",
    "bouleu/s": "boulh/",
    "dekaeth/s": "deke/ths",
    "diake/omai": "dia/keimai",
    "diakrite/os": "diakri/nw",
    "diakwxh/": "diokwxh/",
    "diafi/hmi": "diafre/w",
    "diafugga/nw": "diafeu/gw",
    "di/yos": "di/ya",
    "dw/deka": "duw/deka",
    "e)ggu/qen": "e)ggu/s",
    "ei)ko/tws": "ei)ko/s",
    "e)/qw": "ei)/wqa",
    "e(kato/mpous": "e(kato/mpedos",
    "e)kkre/mamai": "e)kkrema/nnumi",
    "e)kpi/tnw": "e)kpi/ptw",
    "e)mpi/plhmi": "e)mpi/mplhmi",
    "e)mpi/tnw": "e)mpi/ptw",
    "e)napoqnh/skw": "e)napoqnh/|skw",
    "e)ndew=s": "e)ndeh/s",
    "e)neile/w": "e)nei/llw",
    "e)capi/nhs": "e)cai/fnhs",
    "e)pei/seimi": "e)peise/rxomai",
    "e)phlu/ths": "e)/phlus",
    "e)pi/-darqa/nw": "e)pikatadarqa/nw",
    "e)piqalassi/dios": "e)piqala/ssios",
    "e)pimici/a": "e)pimeici/a",
    "e)pipa/reimi1": "e)pipa/reimi2",
    "e)piskepte/os": "e)piskope/w",
    "e)pixeirhte/on": "e)pixeire/w",
    "ei)shghte/os": "ei)shge/omai",
    "ei)spi/tnw": "ei)spi/ptw",
    "ei)s-u(bri/zw": "e)cubri/zw",
    "e)sfore/w": "ei)sfe/rw",
    "e)/sw": "ei)/sw",
    "eu(rete/os": "eu(ri/skw",
    "e)fh/kw": "e)fi/hmi",
    "*)/eforos": "e)/foros",
    "zugo/s": "zugo/n",
    "h)peirw=tis": "h)peirw/ths",
    "i)/sws": "i)/sos",
    "i)te/on": "ei)=mi",
    "*)/isqmia": "i)sqmia/s",
    "kaqairete/os": "kaqaire/w",
    "kai/nw": "ka/neon",
    "kata/-ske/ptomai": "kataskope/w",
    "ke/leuqos": "ke/leusma",
    "khru/kion": "khru/keion",
    "kle/ptw": "e)kkle/ptw",
    "kludw/nion": "klu/dwn",
    "koinane/w": "koinwne/w",
    "loga/des": "loga/s",
    "meqekte/os": "mete/xw",
    "metapempte/os": "metape/mpw",
    "misqoforhte/on": "misqofore/w",
    "moxqe/w": "moxqo/w",
    "muri/os": "mu/rioi",
    "ne/w1": "ne/w",
    "new/s": "nao/s",
    "sumbouleute/os": "sumbouleu/w",
    "sumpi/tnw": "sumpi/ptw",
    "sumpresbeuth/s": "su/mpresbus",
    "sunepilamba/nomai": "sunepilamba/nw",
    "sunei/seimi": "suneise/rxomai",
    "suno/xwka": "sune/xw",
    "su/n-h)qe/w": "sunh/qhs",
    "sunne/w2": "sunne/w",
    "oi)/koqen": "oi)=kos",
    "oi)/koi": "oi)=kos",
    "o(mologoume/nws": "o(mologe/w",
    "o)ce/ws": "o)cu/s2",
    "o(tiou=n": "o(stisou=n",
    "ou(=per": "ou(=",
    "paiwnismo/s": "paianismo/s",
    "panstratia/": "panstratia=|",
    "pa/ntws": "pa=s",
    "paradote/os": "paradi/dwmi",
    "para/-ka/qhmai": "paraka/qhmai",
    "pa/reimi1": "pa/reimi",
    "pare/ceimi": "parece/rxomai",
    "pa/rergos": "pa/rergon",
    "paroxh/": "parokwxh/",
    "patro/qen": "path/r",
    "peziko/s": "pezo/s",
    "pentaeth/s": "pente/ths",
    "perai/teros": "pe/ra",
    "pleuste/os": "ple/w",
    "poihte/os": "poie/w",
    "polemhte/os": "poleme/w",
    "pote/": "pote",
    "po/teros": "po/teron",
    "pri/amai": "pri/asqai",
    "proa/peimi": "proape/rxomai",
    "proapo/llumai": "proapo/llumi",
    "proei=pon": "proagoreu/w",
    "pro/plous": "pro/ploos",
    "prosde/omai": "prosde/w",
    "pro/seimi1,prosi/hmi": "pro/seimi",
    "pro/seimi2,prose/rxomai": "pro/seimi2",
    "prosi/sxw": "prose/xw",
    "pro/s-ka/qhmai": "proska/qhmai",
    "pro/-ske/ptomai": "proskope/w",
    "pro/smicis": "pro/smeicis",
    "prosne/w": "prosneu/w",
    "prossumba/llomai": "prossullamba/nw",
    "prospi/tnw": "prospi/ptw",
    "proste/llw": "prosste/llw",
    "proqe/w2": "proti/qhmi",
    "prw/|raqen": "prw=|ra",
    "r(u/omai": "e)ru/w",
    "skepte/os": "skope/w",
    "ske/ptomai": "skope/w",
    "smikro/s": "mikro/s",
    "spa/rth": "spa/rton",
    "spora/dhn": "spora/s",
    "sto/rennumi": "sto/rnumi",
    "stura/kion": "stu/rac2",
    "tau/th|": "ou(=tos",
    "th=|de": "o(/de",
    "timwrhte/os": "timwre/w",
    "tribh/": "diatribh/",
    "u(/peimi1": "u(/peimi",
    "u(pei=pon": "u(pagoreu/w",
    "u(pe/ceimi": "u(pece/rxomai",
    "u(pomenete/os": "u(pome/nw",
    "u(formi/zomai": "a)formi/zomai",
    "koinwne/w": "koino/w",
    "o(/ph": "ὅπῃ",
    "su/mmiktos": "su/mmeiktos",
    "xalkou=s": "χάλκεος",

    # potential changes to the original file
    "panoikhsi/a|": "panoikesi/a|",
    "paidih/": "paidia/", # **
    "sussw|/zw": "sussw/|zw", # **
    "meso/geia": "meso/gaia", # **
    "e)formi/sis": "e)formh/sis", # **
    "e)pi/-e)pikoure/w": "e)pikoure/w", # **
    "ἔπειμι1": "ἔπειμι", # **
    "ἔξοδος1": "ἔξοδος", # **
    "ἔλεγχος1": "ἔλεγχος", #**
    "ἀβλαβεῖς σπονδαί": "spondh/",
    "ἀμφηρικόν ἀκάτιον": "a)mfhriko/s",
    "ἀμφιδήριτος νίκη": "a)mfidh/ritos",
    "ἀνακῶς ἔχειν": "a)nakw=s",
    "ἀνασχετόν οὐκέτι ποιεῖσθαι": "a)nasxeto/s",
    "ἀνίσχων ἥλιος": "a)ne/xw",
    "ἄπεφθον χρυσίον": "a)/pefqos",
    "ἀργύρεια μέταλλα": "a)rgu/reios",
    "ἀργυρολόγος ναῦς": "a)rgurolo/gos",
    "ἀρχηγέτου ἀπόλλωνος βωμός": "a)rxhge/ths",
    "ἀρχικόν γένος": "a)rxiko/s",
    "ἀστικά διονύσια": "a)stiko/s",
    "δάνεισμα ποιεῖσθαι": "da/neisma",
    "διά τάχους": "ta/xos",
    "δρᾷν τινα": "dra/w",
    "δυσχερές ποιεῖσθαι": "dusxerh/s",
    "ἐλευθέριος ζεύς": "e)leuqe/rios",
    "ἐνθύμιον ποιεῖσθαι": "e)nqu/mios",
    "ἐξαίρετον ποιεῖσθαι": "e)caireto/s",
    "ἐπεκδρομήν ποιεῖσθαι": "e)pekdromh/",
    "ἐπέκπλουν ποιεῖσθαι": "e)pe/kploos",
    "ἐπιμελές ἐστιν": "e)pimelh/s",
    "ἐπιχράω3": "e)pixra/omai",
    "Ἰσθμιάδες σπονδαί": "i)sqmia/s",
    "Κυθηροδίκης ἀρχή": "kuqhrodi/khs",
    "μάθησιν ποιεῖσθαι": "ma/qhsis",
    "ξυμβόλαιος δίκη": "sumbo/laios",
    "παραλία ʽγἦ": "para/lios",
    "πλήθουσα ἀγορά": "plh/qw",
    "σεμναί θεαί": "semno/s",
    "σχεδόν τι": "sxedo/n",
    "τέλεια ἱερά": "te/leios",
    "φορτηγικόν πλοῖον": "forthgiko/s",
}

# hold meanings
meanings = {}

# given an XML string, collapse entries that start with "item" into
# the previous entry
def correctItem(xml):
    # these changes were already made to the file itself
    if False:
        # don't make item it's own item
        xml = re.sub(r'</p>\s*<p>\s*<hi rend="italic">item', ' <hi rend="italic">item', xml)
        # make sure there are spaces between bibliographic entries
        xml = re.sub(r'</bibl><bibl', '</bibl> <bibl', xml)

    return xml

# make minor adjustments to lemmas to add some consistency
def processLemma(l):
    l = re.sub(r'[ὰ]', 'ά', l)
    l = re.sub(r'[ὲ]', 'έ', l)
    l = re.sub(r'[ὴ]', 'ή', l)
    l = re.sub(r'[ὶ]', 'ί', l)
    l = re.sub(r'[ὸ]', 'ό', l)
    l = re.sub(r'[ὺ]', 'ύ', l)
    l = re.sub(r'[ὼ]', 'ώ', l)
    l = re.sub(r'[\.]', '', l)
    return l


# given xml for a single definition entry, extract information for it
def extractDefPart(c, label):
    str = ET.tostring(c, encoding="utf8").decode(encoding="utf8")

    # m = re.search(r'\[[^\]]*\d+\.\d+\.\d+[^\]]*\]', str)
    # if (m):
    #     print(str)
    #     print("---")
    # if re.search(r'item', str):# and not(re.search(r'<ns0:p xmlns:ns0="http://www\.tei-c\.org/ns/1\.0">\s*<ns0:hi rend="italic">', str)):
    #     print(str)

    # remove starting XML tag
    str = re.sub(r'<\?xml[^>]+\?>', '', str)

    # remove notation from within tag
    str = re.sub(r'ns0:', '', str)

    # remove paragraph tags
    str = re.sub(r'<p[^>]+>', '', str)
    str = re.sub(r'</p>', '', str)

    # remove empty quote notation
    str = re.sub(r'<quote />', ' (quote) ', str)

    # contract newlines
    str = re.sub(r'\n', ' ', str)

    # remove extra notation from bibliography tags
    str = re.sub(r' n="[Tt]huc. [^"]+"', '', str)

    # remove italic and foreign language tags
    str = re.sub(r'<hi rend="italic">', '', str)
    str = re.sub(r'</hi>', ' ', str)
    str = re.sub(r'<foreign[^>]+>', '', str)
    str = re.sub(r'</foreign>', ' ', str)

    # remove multi spaces
    str = re.sub(r'\s+', ' ', str)

    split = str.split("<bibl>")

    start = split[0]

    refList = []

    for s in split[1:]:
        split2 = s.split("</bibl>")
        ref = split2[0]
        link = re.sub(r'\.', "/", ref)
        note = split2[1]
        o = {
            "ref": ref,
            "refLink": link,
            "note": note,
        }
        #print(ref, label)
        meanings[ref] = label
        refList.append(o)

    obj = {
        "text": {
            "start": start,
            "refList": refList,
            "identifier": label,
            "keyPassageList": []
        },
        "subList": []
    }

    return obj

# given XML for a word, extract information about that word.
def extractWordInfo(word, lemma, parentLabel, depth=0):
    if (depth == 0):
        start = lemma + " "
    else:
        start = ""
    fullDef = []
    if (len(word) > 2) and (word[1].tag == "{http://www.tei-c.org/ns/1.0}p") and (word[2].tag == "{http://www.tei-c.org/ns/1.0}div"):
        # head plus info, then children
        label = parentLabel + LEVEL_ITEMS[depth][0]
        obj = extractDefPart(word[1], label)
        obj["text"]["start"] = start + obj["text"]["start"]

        for i, c in enumerate(word[2:]):
            label = parentLabel + LEVEL_ITEMS[depth][i]
            defPart = extractWordInfo(c, lemma, label + ".", depth+1)
            fullDef.append(defPart)

        obj["subList"] = fullDef
        # return the created object
        return obj
    else:
        # head, possible children
        for i, c in enumerate(word[1:]):
            label = parentLabel + LEVEL_ITEMS[depth][i]
            if (c.tag == "{http://www.tei-c.org/ns/1.0}div"):
                defPart = extractWordInfo(c, lemma, label + ".", depth+1)
            else:
                defPart = extractDefPart(c, label)
            fullDef.append(defPart)

    myLabel = parentLabel
    if len(myLabel) > 0:
        myLabel = myLabel[:-1]

    obj = {
        "text": {
            "start": start,
            "refList": [],
            "identifier": myLabel,
            "keyPassageList": []
        },
        "subList": fullDef
    }

    return obj

# back engineer raw article
def generateRaw(obj):
    res = []

    identifier = obj["text"]["identifier"]
    if (identifier == ""):
        txt = ""
    else:
        txt = identifier + ". "
    txt += obj["text"]["start"]
    for ref in obj["text"]["refList"]:
        txt += ref["ref"] + ref["note"] + " "
    res.append(txt)

    for sub in obj["subList"]:
        res.append("  " + generateRaw(sub).replace("\n", "\n  "))

    return "\n".join(res)

# ===================

xml = utils.getContent("input/betant_thuclex.xml", False)
xml = correctItem(xml)

# grab all of the lines in the document
try:
    root = ET.fromstring(xml)
except ET.ParseError as err:
    print(xml)
    print(err)
    raise("Failed")

body = root[1][0]

res = {}
i = 0
for textpart in body:
    if textpart.tag == "{http://www.tei-c.org/ns/1.0}div":
        i += 1
        head = textpart[0][0].text
        # print(head)
        # print("=======")
        words = textpart[1:]
        for word in words:
            lemma = processLemma(word[0][0].text)
            key = lemma
            if 'n' in word.attrib:
                key = word.attrib['n'][4:]
            if lemma == "πρῶτος":
                key = "prw=tos"

            meanings = {}
            obj = extractWordInfo(word, lemma, "")
            obj["text"]["lem"] = lemma

            # replace keys
            if key in keyMap:
                key = keyMap[key]

            if lemma == "χρῆσθαι":
                key = "xra/omai"
            elif lemma == "ὁποτεροσοῦν":
                key = "o(poterosou=n"

            if (key != "pari/hmi" and lemma == "παριέναι"):
                key = "pa/reimi2"

            #print(lemma)
            #print(obj)
            #print("------")
            if not(key in res):
                res[key] = []



            raw = generateRaw(obj);

            res[key].append((raw, [obj], meanings))




        # print("========================================================")
        # print("========================================================")

# recursively add a prefix to identifiers
def recursivePrefix(o, prefix):
    if not(o["text"]["identifier"] == ""):
        o["text"]["identifier"] = prefix + o["text"]["identifier"]
    for s in o["subList"]:
        recursivePrefix(s, prefix)


# unify words that have multiple definitions
printMeanings = False
newRes = {}
for key in res:
    lst = res[key]

    if len(lst) > 1:
        if printMeanings:
            print(key, len(lst))
        defs = []
        meanings = {}

        # make sure items are in the proper order and have the proper lemma name
        if (key == "sw/frwn" or key == "fu/w" or key == "penthkontou/ths"
              or key == "pezo/s" or key == "mimnh/skw" or key == "miai/nw"
              or key == "katafanh/s" or key == "katei/rgw" or key == "kaqe/zomai"
              or key == "i)/dios" or key == "eu)qu/s"
              or key == "e)/leos" or key == "e)kpreph/s" or key == "e)kplh/ssw"
              or key == "gh=" or key == "braxu/s" or key == "bradu/s"
              or key == "a)/ristos" or key == "a)pra/gmwn"
              or key == "a)nu/poptos" or key == "a)ni/hmi" or key == "a)/llote"
              or key == "h(du/s" or key == "xra/w2" or key == "melito/w"
              or key == "eu)qu/s" or key == "a)pokinduneu/w" or key == "spondh/"
              or key == "a)/pwqen" or key == "diafqei/rw" or key == "skope/w"
              or key == "proskope/w" or key == "proagoreu/w" or key == "pa=s"
              or key == "o)cu/s2" or key == "oi)=kos" or key == "meso/gaia"
              or key == "melito/w" or key == "mete/xw" or key == "e)fi/hmi"
              or key == "eu(ri/skw" or key == "e)piqala/ssios" or key == "e)nanti/os"
              or key == "e)ggu/s" or key == "ta/xos" or key == "e)po/mnumi"
              or key == "a)nte/xw"):
            lst.reverse()
        elif key == "sta/dion" or key == "o(po/sos":
            newList = []
            newList.append(lst[1])
            newList.append(lst[2])
            newList.append(lst[0])
            lst = newList
        elif key == "a)ni/hmi" or key == "presbeuth/s":
            newList = []
            newList.append(lst[0])
            newList.append(lst[2])
            newList.append(lst[1])
            lst = newList
        elif key == "mo/nos" or key == "koino/s":
            newList = []
            newList.append(lst[1])
            newList.append(lst[0])
            newList.append(lst[2])
            lst = newList
        elif key == "taxu/s":
            newList = []
            newList.append(lst[2])
            newList.append(lst[1])
            newList.append(lst[0])
            lst = newList
        elif key == "a)/llos":
            newList = []
            newList.append(lst[3])
            newList.append(lst[6])
            newList.append(lst[0])
            newList.append(lst[1])
            newList.append(lst[2])
            newList.append(lst[4])
            newList.append(lst[5])
            lst = newList


        raws = []
        for i, item in enumerate(lst):
            prefix = ""#"%d-" % i
            r, o, m = item
            obj = o[0]
            recursivePrefix(obj, prefix)
            name = obj["text"]["lem"]
            obj["text"]["identifier"] = name
            if printMeanings:
                print(name)
            #print(obj)
            defs.append(obj)
            for k in m:
                meanings[k] = prefix + m[k]
            #
            #print(o[0]["text"]["start"])
            raws.append("======---%s---======" % name)
            raws.append(r)


        if printMeanings:
            print("=======")
        newRes[key] = ("\n".join(raws), defs, meanings)#lst[0]
    else:
        newRes[key] = lst[0]

utils.safeWrite("results/betant.json", newRes, dumpJSON=True)
