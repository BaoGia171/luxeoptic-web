"""Generate editable diagrams.net sources and matching PNG previews (Pillow)."""
from pathlib import Path
import xml.etree.ElementTree as E
from PIL import Image,ImageDraw,ImageFont
import math
OUT=Path(__file__).resolve().parents[1]/'docs/design';OUT.mkdir(parents=True,exist_ok=True)
FONT='C:/Windows/Fonts/arial.ttf';BOLD='C:/Windows/Fonts/arialbd.ttf'
class Diagram:
 def __init__(self,name,title,w,h):
  self.name=name;self.w=w;self.h=h;self.nodes={};self.edges=[]
  self.node('title',title,30,20,w-60,60,'text',30)
 def node(self,key,text,x,y,w,h,kind='box',size=21):self.nodes[key]=(text,x,y,w,h,kind,size);return key
 def edge(self,source,target,points,label='',labelat=None):self.edges.append((source,target,points,label,labelat))
 def save(self):
  image=Image.new('RGB',(self.w,self.h),'white');d=ImageDraw.Draw(image)
  mx=E.Element('mxfile',host='app.diagrams.net');diagram=E.SubElement(mx,'diagram',name=self.name);model=E.SubElement(diagram,'mxGraphModel',page='1',pageWidth=str(self.w),pageHeight=str(self.h));root=E.SubElement(model,'root');E.SubElement(root,'mxCell',id='0');E.SubElement(root,'mxCell',id='1',parent='0')
  def text_draw(text,box,size,bold=False):
   x,y,w,h=box;font=ImageFont.truetype(BOLD if bold else FONT,size);lines=[]
   for para in text.split('\n'):
    line=''
    for word in para.split(' '):
     if d.textlength((line+' '+word).strip(),font=font)>w-16 and line:lines.append(line);line=word
     else:line=(line+' '+word).strip()
    lines.append(line)
   top=y+(h-len(lines)*(size+6))/2
   for line in lines:d.text((x+(w-d.textlength(line,font=font))/2,top),line,font=font,fill='#173549');top+=size+6
  for n,(src,tgt,points,label,at) in enumerate(self.edges):
   d.line(points,fill='#526b79',width=3);x,y=points[-1];px,py=points[-2];angle=math.atan2(y-py,x-px);d.polygon([(x,y),(x-13*math.cos(angle-.5),y-13*math.sin(angle-.5)),(x-13*math.cos(angle+.5),y-13*math.sin(angle+.5))],fill='#526b79')
   cell=E.SubElement(root,'mxCell',id='e'+str(n),value='',style='edgeStyle=none;endArrow=block;strokeColor=#526b79;',edge='1',parent='1',source=src,target=tgt);geo=E.SubElement(cell,'mxGeometry',relative='1',attrib={'as':'geometry'});arr=E.SubElement(geo,'Array',attrib={'as':'points'})
   for px,py in points[1:-1]:E.SubElement(arr,'mxPoint',x=str(px),y=str(py))
   if label and at:
    self.node('label'+str(n),label,*at,'text',18)
  for key,(text,x,y,w,h,kind,size) in self.nodes.items():
   fill='#f1f6f8';style=f'whiteSpace=wrap;html=0;fontFamily=Arial;fontSize={size};fontColor=#173549;strokeColor=#526b79;fillColor={fill};'
   if kind=='text':style+='text;strokeColor=none;fillColor=none;'
   elif kind=='diamond':d.polygon([(x+w/2,y),(x+w,y+h/2),(x+w/2,y+h),(x,y+h/2)],fill='#fff3d5',outline='#526b79',width=2);style+='rhombus;fillColor=#fff3d5;'
   elif kind in ('start','end'):
    d.ellipse((x,y,x+w,y+h),fill='#173549' if kind=='start' else 'white',outline='#173549',width=3)
    if kind=='end':d.ellipse((x+7,y+7,x+w-7,y+h-7),fill='#173549')
    style+='ellipse;fillColor=#173549;' if kind=='start' else 'shape=doubleEllipse;fillColor=#173549;'
   else:d.rounded_rectangle((x,y,x+w,y+h),radius=14,fill=fill,outline='#526b79',width=2);style+='rounded=1;'
   if kind not in ('start','end'):text_draw(text,(x,y,w,h),size,key=='title')
   cell=E.SubElement(root,'mxCell',id=key,value=text,style=style,vertex='1',parent='1');E.SubElement(cell,'mxGeometry',x=str(x),y=str(y),width=str(w),height=str(h),attrib={'as':'geometry'})
  E.indent(mx);E.ElementTree(mx).write(OUT/(self.name+'.drawio'),encoding='utf-8',xml_declaration=True);image.save(OUT/(self.name+'.png'))

s=Diagram('03-sitemap','SITEMAP — LUXEOPTIC',1440,1060)
s.node('home','Trang chủ\nindex.php',520,115,380,90)
s.node('catalog','Danh sách sản phẩm\nproducts.php\nTìm / lọc / sắp xếp / phân trang',30,300,400,120)
s.node('account','Đăng nhập / Đăng ký\nauth.php',520,300,380,120)
s.node('admin','Quản trị (chỉ Admin)\nadmin/index.php',1010,300,400,120)
s.node('detail','Chi tiết sản phẩm\nproduct-detail.php?id=…',30,490,400,100)
s.node('compare','So sánh hai gọng\ncompare.php?id1=…&id2=…',30,660,400,100)
s.node('cart','Giỏ hàng & đặt hàng\ncart.php',520,490,380,100)
s.node('profile','Hồ sơ cá nhân\nprofile.php',520,660,380,100)
s.node('orders','Lịch sử / chi tiết đơn của mình\nmy-orders.php?id=…',520,830,380,100)
s.node('management','admin/products.php — Sản phẩm\nadmin/categories.php — Danh mục\nadmin/orders.php — Đơn hàng\nadmin/users.php — Tài khoản',1010,490,400,270)
for a,b,pts in [('home','catalog',[(710,205),(710,250),(230,250),(230,300)]),('home','account',[(710,205),(710,300)]),('account','admin',[(900,360),(1010,360)]),('catalog','detail',[(230,420),(230,490)]),('catalog','compare',[(30,360),(15,360),(15,710),(30,710)]),('detail','cart',[(430,540),(520,540)]),('account','profile',[(900,360),(950,360),(950,710),(900,710)]),('profile','orders',[(710,760),(710,830)]),('cart','orders',[(900,540),(975,540),(975,880),(900,880)]),('admin','management',[(1210,420),(1210,490)])]:s.edge(a,b,pts)
s.node('note','Mũi tên thể hiện đường điều hướng chính, không phải quan hệ dữ liệu.\nGiỏ dùng được khi chưa đăng nhập; đặt hàng / hồ sơ / lịch sử cần đăng nhập.\nChatbot là khung nổi trên trang khách; actions/*.php là xử lý, không phải trang giao diện.',30,945,1380,100,'text',20);s.save()

a=Diagram('04-checkout-activity','ACTIVITY — GIỎ HÀNG VÀ ĐẶT HÀNG',1240,1750)
a.node('start','',580,100,35,35,'start');a.node('cart','Xem sản phẩm → thêm / sửa giỏ',380,175,440,70)
a.node('login','Đã đăng nhập?',490,290,220,105,'diamond',19)
a.node('auth','Đăng nhập\nreturn=cart',900,300,280,80)
a.node('form','Nhập giao nhận, chọn COD / chuyển khoản\nGửi POST + CSRF + checkout_token',380,450,440,90)
a.node('valid','Dữ liệu hợp lệ?',490,585,220,105,'diamond',19)
a.node('error','Báo lỗi gần ô nhập\nGiữ thông tin và giỏ\nKhách sửa rồi gửi lại',30,590,300,110)
a.node('lock','BEGIN transaction\nKhóa sản phẩm theo ID tăng dần\nĐọc giá / trạng thái / stock từ DB',380,750,440,110)
a.node('stock','Được bán & đủ kho?',470,910,260,110,'diamond',18)
a.node('write','Tính tiền, voucher LUXE10\nINSERT orders + order_items (snapshot)\nUPDATE stock = stock − quantity',380,1080,440,110)
a.node('success','Ghi dữ liệu thành công?',460,1240,280,110,'diamond',17)
a.node('rollback','ROLLBACK\nGiữ giỏ; báo lỗi an toàn\nCho phép thử lại',900,1120,280,120)
a.node('commit','COMMIT → xóa giỏ / voucher / token\nChuyển my-orders.php?id=đơn_vừa_tạo',380,1410,440,90)
a.node('end','',580,1550,35,35,'end');a.node('errend','',160,780,35,35,'end');a.node('failend','',1020,1330,35,35,'end')
a.edge('start','cart',[(598,135),(598,175)]);a.edge('cart','login',[(598,245),(598,290)]);a.edge('login','auth',[(710,342),(900,342)],'[Chưa]',(740,305,150,35));a.node('mergeLogin','',585,410,30,30,'diamond');a.edge('auth','mergeLogin',[(1040,380),(1040,425),(615,425)]);a.edge('login','mergeLogin',[(600,395),(600,410)],'[Có]',(480,395,85,40));a.edge('mergeLogin','form',[(600,440),(600,450)]);a.edge('form','valid',[(600,540),(600,585)]);a.edge('valid','error',[(490,637),(330,637)],'[Sai]',(350,600,120,35));a.edge('error','errend',[(180,700),(180,780)]);a.edge('valid','lock',[(600,690),(600,750)],'[Đúng]',(610,700,110,35));a.edge('lock','stock',[(600,860),(600,910)]);a.edge('stock','write',[(600,1020),(600,1080)],'[Có]',(610,1030,85,35));a.node('mergeError','',1025,1075,30,30,'diamond');a.edge('stock','mergeError',[(730,965),(1040,965),(1040,1075)],'[Không]',(850,920,140,40));a.edge('write','success',[(600,1190),(600,1240)]);a.edge('success','mergeError',[(740,1295),(860,1295),(860,1090),(1025,1090)],'[Lỗi]',(740,1250,90,35));a.edge('success','commit',[(600,1350),(600,1410)],'[Đúng]',(610,1360,100,35));a.edge('commit','end',[(600,1500),(600,1550)]);a.edge('mergeError','rollback',[(1040,1105),(1040,1120)]);a.edge('rollback','failend',[(1040,1240),(1040,1330)])
a.node('note','Các nút kết thúc là kết thúc một lượt gửi yêu cầu, không xóa giỏ khi thất bại.\nChỉ xóa giỏ sau COMMIT. Token đã dùng bị từ chối; khóa dòng ngăn bán vượt tồn kho.\nLỗi DB bất kỳ trong transaction đều ROLLBACK; chi tiết lỗi chỉ ghi ở log server.',50,1630,1140,100,'text',20);a.save()

s=Diagram('06-order-state','STATE MACHINE — TRẠNG THÁI ĐƠN HÀNG',1460,930)
s.node('start','',35,230,35,35,'start')
for key,label,x in [('pending','pending\nChờ xác nhận',100),('confirmed','confirmed\nĐã xác nhận',440),('shipping','shipping\nĐang giao',780),('completed','completed\nHoàn tất',1120)]:s.node(key,label,x,185,250,120)
s.node('cancelled','cancelled — Đã hủy\nentry / hoàn kho đúng một lần',320,560,400,110)
s.node('end','',1210,580,45,45,'end')
s.edge('start','pending',[(70,248),(100,248)])
s.edge('pending','confirmed',[(350,245),(440,245)],'Admin xác nhận',(330,130,140,50))
s.edge('confirmed','shipping',[(690,245),(780,245)],'Admin giao hàng',(670,130,140,50))
s.edge('shipping','completed',[(1030,245),(1120,245)],'Admin hoàn tất',(1010,130,140,50))
s.edge('pending','cancelled',[(220,305),(220,615),(320,615)],'Khách sở hữu / Admin hủy\n[unpaid]',(15,365,190,130))
s.edge('confirmed','cancelled',[(565,305),(565,560)],'Admin hủy\n[unpaid]',(590,410,180,65))
s.edge('completed','end',[(1245,305),(1245,580)])
s.edge('cancelled','end',[(720,615),(1210,615)])
s.node('guard','Điều kiện trước khi giao:\nCOD hoặc bank_transfer đã paid',740,335,310,100,'text',20)
s.node('cod','Khi COD hoàn tất:\nđặt payment_status = paid\ncùng transaction',860,445,330,105,'text',20)
s.node('notes','Admin xác nhận nhận tiền: chỉ cập nhật unpaid → paid khi đơn chưa kết thúc (không đổi status).\nMọi cập nhật khóa cùng dòng orders trong transaction; đọc lại trạng thái trước khi quyết định.\nHủy chỉ từ pending / confirmed và unpaid; hoàn kho + status cùng transaction.\ncompleted / cancelled là trạng thái cuối: không mở lại, không paid → unpaid.',40,730,1380,155,'text',22);s.save()
print('Created three editable diagrams and PNG previews.')
