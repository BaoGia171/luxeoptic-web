"""Reconstruct TV4's detailed use case as editable draw.io and PNG."""
from pathlib import Path
import xml.etree.ElementTree as ET
from PIL import Image, ImageDraw, ImageFont

OUT = Path(__file__).resolve().parents[1] / 'docs/design'
W, H = 1400, 1000
im = Image.new('RGB', (W, H), 'white')
d = ImageDraw.Draw(im)
ink = '#273743'
mx = ET.Element('mxfile', host='app.diagrams.net')
dg = ET.SubElement(mx, 'diagram', name='TV4 - Cart and checkout')
model = ET.SubElement(dg, 'mxGraphModel', page='1', pageWidth=str(W), pageHeight=str(H))
root = ET.SubElement(model, 'root')
ET.SubElement(root, 'mxCell', id='0')
ET.SubElement(root, 'mxCell', id='1', parent='0')

def node(key, text, x, y, w, h, shape='text', size=25):
    style = f'html=0;whiteSpace=wrap;fontFamily=Arial;fontSize={size};fontColor={ink};strokeColor={ink};fillColor=#FFFFFF;'
    style += {'text':'text;strokeColor=none;fillColor=none;', 'ellipse':'ellipse;', 'actor':'shape=umlActor;verticalLabelPosition=bottom;verticalAlign=top;', 'box':''}[shape]
    c = ET.SubElement(root, 'mxCell', id=key, value=text, style=style, vertex='1', parent='1')
    ET.SubElement(c, 'mxGeometry', x=str(x), y=str(y), width=str(w), height=str(h), attrib={'as':'geometry'})
    if shape == 'ellipse': d.ellipse((x,y,x+w,y+h), fill='#F5F7F8', outline=ink, width=3)
    if shape == 'box': d.rectangle((x,y,x+w,y+h), outline=ink, width=3)
    if shape == 'actor':
        cx=x+w/2
        d.ellipse((cx-20,y,cx+20,y+40),outline=ink,width=3)
        d.line([(cx,y+40),(cx,y+100)],fill=ink,width=3)
        d.line([(x,y+62),(x+w,y+62)],fill=ink,width=3)
        d.line([(x,y+h),(cx,y+100),(x+w,y+h)],fill=ink,width=3)
        y += h+15; h=75; x-=90; w+=180
    if shape != 'box':
        font=ImageFont.truetype('C:/Windows/Fonts/arial.ttf',size)
        lines=text.split('\n'); top=y+(h-len(lines)*(size+8))/2
        for line in lines:
            d.text((x+(w-d.textlength(line,font=font))/2,top),line,font=font,fill=ink)
            top+=size+8

node('boundary','',370,115,660,760,'box')
node('title','USE CASE CHI TIẾT — GIỎ HÀNG VÀ ĐẶT HÀNG',30,20,1340,65,size=34)
node('system','Phân hệ giỏ hàng và đặt hàng',400,130,600,50,size=28)
node('guest','Khách chưa\nđăng nhập',130,395,90,140,'actor')
node('customer','Khách hàng\nđã đăng nhập',1180,395,90,140,'actor')
cases=[('uc1','UC01 — Thêm sản phẩm\nvào giỏ',220),('uc2','UC02 — Xem, cập nhật\nvà xóa sản phẩm trong giỏ',385),('uc3','UC03 — Áp dụng voucher',550),('uc4','UC04 — Đặt hàng',715)]
for key, text, y in cases: node(key,text,455,y,490,110,'ellipse')
for i, (actor, targets) in enumerate([('guest',cases[:3]),('customer',cases)]):
    for j,(target,_,y) in enumerate(targets):
        sx=220 if actor=='guest' else 1180; sy=457
        tx=455 if actor=='guest' else 945; ty=y+55
        d.line([(sx,sy),(tx,ty)],fill=ink,width=2)
        c=ET.SubElement(root,'mxCell',id=f'e{i}{j}',style=f'endArrow=none;startArrow=none;strokeColor={ink};',edge='1',parent='1',source=actor,target=target)
        ET.SubElement(c,'mxGeometry',relative='1',attrib={'as':'geometry'})
node('note','Đặt hàng yêu cầu đăng nhập. Voucher là tùy chọn, không bắt buộc để đặt hàng.\nĐường nối là quan hệ tương tác; trình tự xử lý nằm ở Activity Diagram.',30,890,1340,90,size=22)
ET.indent(mx)
ET.ElementTree(mx).write(OUT/'04a-cart-checkout-use-case.drawio',encoding='utf-8',xml_declaration=True)
im.save(OUT/'04a-cart-checkout-use-case.png')
print('Created editable use case and PNG.')
