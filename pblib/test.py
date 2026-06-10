from playbasis import Playbasis
import pprint

pb = Playbasis()
pp = pprint.PrettyPrinter(indent=4)
res = pb.auth('YOUR_API_KEY', 'YOUR_API_SECRET')
print res
res = pb.ranks(5)
pp.pprint(res)
